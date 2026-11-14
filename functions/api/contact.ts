/**
 * Cloudflare Pages Function: POST /api/contact
 *
 * Verifies a Cloudflare Turnstile token server-side, then relays the message to email via Resend.
 * Everything else on the site stays static; this only runs on POSTs to /api/contact.
 *
 * Errors come back as short codes (`{ ok: false, error: 'missing_fields' }`). The page maps them to
 * the messages in src/data/ui.json (contact.errors), so all user-facing copy stays in content.
 *
 * Environment (Pages project → Settings → Variables and Secrets):
 *   TURNSTILE_SECRET     (secret)   Turnstile widget secret key
 *   TURNSTILE_HOSTNAMES  (text)     Comma-separated hostnames the widget may be solved on; each
 *                                   entry also allows its subdomains (e.g. preview deployments)
 *   RESEND_API_KEY       (secret)   Resend API key with send access
 *   CONTACT_TO_EMAIL     (text)     Inbox(es) that receive inquiries, comma-separated
 *   CONTACT_FROM_EMAIL   (text)     Optional sender on a Resend-verified domain
 */

interface Env {
  TURNSTILE_SECRET?: string;
  TURNSTILE_HOSTNAMES?: string;
  RESEND_API_KEY?: string;
  CONTACT_TO_EMAIL?: string;
  CONTACT_FROM_EMAIL?: string;
}

interface Context {
  request: Request;
  env: Env;
}

interface TurnstileResult {
  success: boolean;
  action?: string;
  hostname?: string;
  'error-codes'?: string[];
}

const EXPECTED_ACTION = 'contact';
const MAX_BODY_BYTES = 32 * 1024;
const DEFAULT_FROM = 'Paradox website <onboarding@resend.dev>';
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
// Cloudflare's documented always-pass / always-fail test secrets. Their siteverify responses
// carry a placeholder hostname and no action, so those checks only apply to real secrets.
const TEST_SECRETS = new Set(['1x0000000000000000000000000000000AA', '2x0000000000000000000000000000000AA', '3x0000000000000000000000000000000AA']);

const LIMITS = { name: 200, email: 320, company: 200, message: 5000 } as const;
// Offer ids are content filenames (src/content/offers/<id>.md); anything else is dropped.
const OFFER_PATTERN = /^[a-z0-9][a-z0-9-]{0,63}$/;

type ErrorCode =
  | 'too_large'
  | 'not_configured'
  | 'bad_request'
  | 'missing_fields'
  | 'too_long'
  | 'invalid_email'
  | 'spam_check'
  | 'send_failed';

type Fields = Record<'name' | 'email' | 'company' | 'offer' | 'message' | 'honeypot' | 'token', string>;

export const onRequestPost = async ({ request, env }: Context): Promise<Response> => {
  const wantsRedirect = isPlainFormPost(request);
  const reply = (status: number, body: { ok: boolean; error?: ErrorCode }) =>
    wantsRedirect ? redirectBack(request, body) : json(status, body);

  if (Number(request.headers.get('content-length') ?? 0) > MAX_BODY_BYTES) {
    return reply(413, { ok: false, error: 'too_large' });
  }

  if (!env.TURNSTILE_SECRET || !env.RESEND_API_KEY || !env.CONTACT_TO_EMAIL) {
    console.error('contact: missing TURNSTILE_SECRET, RESEND_API_KEY, or CONTACT_TO_EMAIL');
    return reply(503, { ok: false, error: 'not_configured' });
  }

  let fields: Fields;
  try {
    fields = await readFields(request);
  } catch {
    return reply(400, { ok: false, error: 'bad_request' });
  }

  // Bots that fill the hidden field get a normal-looking success and no email.
  if (fields.honeypot) return reply(200, { ok: true });

  const problem = validate(fields);
  if (problem) return reply(422, { ok: false, error: problem });

  const verified = await verifyTurnstile(fields.token, request, env);
  if (!verified) return reply(403, { ok: false, error: 'spam_check' });

  const sent = await sendEmail(fields, env);
  if (!sent) return reply(502, { ok: false, error: 'send_failed' });

  return reply(200, { ok: true });
};

async function readFields(request: Request): Promise<Fields> {
  const type = request.headers.get('content-type') ?? '';
  let get: (key: string) => unknown;

  if (type.includes('application/json')) {
    const data = (await request.json()) as Record<string, unknown>;
    get = (key) => data[key];
  } else if (type.includes('application/x-www-form-urlencoded') || type.includes('multipart/form-data')) {
    const data = await request.formData();
    get = (key) => data.get(key);
  } else {
    throw new Error('Unsupported content type');
  }

  const str = (key: string) => {
    const value = get(key);
    return typeof value === 'string' ? value.trim() : '';
  };

  return {
    name: str('name'),
    email: str('email'),
    company: str('company'),
    offer: OFFER_PATTERN.test(str('offer')) ? str('offer') : '',
    message: str('message'),
    honeypot: str('website'),
    token: str('cf-turnstile-response') || str('turnstileToken'),
  };
}

function validate({ name, email, company, message }: Fields): ErrorCode | null {
  if (!name || !email || !message) return 'missing_fields';
  if (
    name.length > LIMITS.name ||
    email.length > LIMITS.email ||
    company.length > LIMITS.company ||
    message.length > LIMITS.message
  ) {
    return 'too_long';
  }
  if (!EMAIL_PATTERN.test(email)) return 'invalid_email';
  return null;
}

async function verifyTurnstile(token: string, request: Request, env: Env): Promise<boolean> {
  if (!token || token.length > 2048) return false;

  const secret = env.TURNSTILE_SECRET!;
  const body = new FormData();
  body.append('secret', secret);
  body.append('response', token);
  body.append('idempotency_key', crypto.randomUUID());
  const ip = request.headers.get('CF-Connecting-IP');
  if (ip) body.append('remoteip', ip);

  let result: TurnstileResult;
  try {
    const res = await fetch('https://challenges.cloudflare.com/turnstile/v0/siteverify', { method: 'POST', body });
    result = (await res.json()) as TurnstileResult;
  } catch (err) {
    console.error('contact: Turnstile siteverify request failed', err);
    return false;
  }

  if (!result.success) {
    console.warn('contact: Turnstile rejected token', result['error-codes']);
    return false;
  }
  if (TEST_SECRETS.has(secret)) return true;

  if (result.action !== EXPECTED_ACTION) {
    console.warn('contact: unexpected Turnstile action', result.action);
    return false;
  }

  const allowed = (env.TURNSTILE_HOSTNAMES ?? new URL(request.url).hostname)
    .split(',')
    .map(normalizeHostname)
    .filter(Boolean);
  if (!result.hostname || !isAllowedHostname(result.hostname, allowed)) {
    console.warn('contact: Turnstile hostname not allowed', result.hostname);
    return false;
  }

  return true;
}

function normalizeHostname(host: string): string {
  return host.trim().toLowerCase().replace(/\.+$/, '');
}

// Exact match, or a subdomain on a "." boundary: "paradox-cnd.pages.dev" allows
// "abc123.paradox-cnd.pages.dev" but not "evil-paradox-cnd.pages.dev".
function isAllowedHostname(hostname: string, allowed: string[]): boolean {
  const host = normalizeHostname(hostname);
  return allowed.some((entry) => host === entry || host.endsWith(`.${entry}`));
}

async function sendEmail({ name, email, company, offer, message }: Fields, env: Env): Promise<boolean> {
  const to = env.CONTACT_TO_EMAIL!.split(',').map((s) => s.trim()).filter(Boolean);
  const safeName = name.replace(/[\r\n]+/g, ' ');
  const text = [
    `Name: ${safeName}`,
    `Email: ${email}`,
    `Business: ${company || '(not given)'}`,
    `Interested in: ${offer || '(not sure yet)'}`,
    '',
    message,
  ].join('\n');

  try {
    const res = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${env.RESEND_API_KEY}`,
        'Content-Type': 'application/json',
        'Idempotency-Key': crypto.randomUUID(),
      },
      body: JSON.stringify({
        from: env.CONTACT_FROM_EMAIL || DEFAULT_FROM,
        to,
        reply_to: email,
        subject: offer ? `New inquiry (${offer}) from ${safeName}` : `New inquiry from ${safeName}`,
        text,
      }),
    });
    if (!res.ok) {
      console.error('contact: Resend responded', res.status);
      return false;
    }
    return true;
  } catch (err) {
    console.error('contact: Resend request failed', err);
    return false;
  }
}

function isPlainFormPost(request: Request) {
  const type = request.headers.get('content-type') ?? '';
  const accept = request.headers.get('accept') ?? '';
  return !type.includes('application/json') && accept.includes('text/html');
}

function redirectBack(request: Request, { ok, error }: { ok: boolean; error?: ErrorCode }) {
  const url = new URL('/contact/', request.url);
  url.searchParams.set('status', ok ? 'sent' : 'error');
  if (error) url.searchParams.set('code', error);
  return Response.redirect(url.toString(), 303);
}

function json(status: number, body: { ok: boolean; error?: ErrorCode }) {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store' },
  });
}
