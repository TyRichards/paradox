# Paradox

Static site for Paradox, Ty Richards' business process & automation agency. It replaces the 2015 Paradox Creative WordPress site. The archived theme is at [TyRichards/paradox-2015](https://github.com/TyRichards/paradox-2015) for reference only.

## Stack

- **[Astro 7](https://astro.build/)** + **TypeScript** (strict), static output (`dist/`)
- **[Lumos for Astro](https://github.com/lumosframework/lumos-for-astro)** (MIT, Timothy Ricks): plain CSS custom properties and a few Astro primitives. No Tailwind.
- **Markdown content collections** as the source of truth (`src/content/`, schemas in `src/content.config.ts`)
- **[Sveltia CMS](https://sveltiacms.app/en/)**: a Git-based editor over the same files, at `/admin/`. No database.
- **Cloudflare Pages** (free plan): static hosting, with `_redirects` / `_headers` in `public/`. One Pages Function (`functions/api/contact.ts`) handles contact-form POSTs on the free Functions quota. No D1, R2, or paid Workers.

## Quick start

Requires Node **22.23.4+** (pinned in `.node-version`).

```sh
npm install
npm run dev        # http://localhost:4321
npm run build      # validates all content against the schemas and writes dist/
npm run preview    # serve dist/ locally
npm run check      # astro check (TypeScript + .astro diagnostics)
```

## Site map

| Route | Source |
| --- | --- |
| `/` | `src/content/pages/home.md` + offers, published work, latest posts |
| `/pricing/` | `src/content/pages/pricing.md` + `src/content/offers/*.md` |
| `/work/`, `/work/<slug>/` | `src/content/work/*.md`, **published entries only** |
| `/about/` | `src/content/pages/about.md` |
| `/contact/` | `src/content/pages/contact.md` + the contact form (posts to `/api/contact`, see [Contact form](#contact-form)) |
| `/privacy/` | `src/content/pages/privacy.md` |
| `/blog/`, `/blog/<slug>/`, `/rss.xml` | `src/content/blog/*.md`. Blog is hidden from the nav, footer, and sitemap until a post exists. |
| `/admin/` | Sveltia CMS (`public/admin/`) |

`/services` and `/services/*` redirect to `/pricing/` (see `public/_redirects`).

**Where copy lives.** Every user-facing string is in a content file, never in a component:

- Page copy (headings, ledes, buttons, section headings, closing CTAs): `src/content/pages/*.md`
- Pricing cards: `src/content/offers/*.md`. The filename is the offer id, so `process-audit.md` is linked as `/contact/?offer=process-audit`, which preselects it in the contact form.
- Site name, default meta, nav, and header button: `src/data/site.json` (schema `src/lib/site.ts`)
- Interface text (footer, form labels and errors, work/blog labels, 404): `src/data/ui.json` (schema `src/lib/ui.ts`)

All of it is editable in the CMS. The page sections (`src/components/sections/`: Hero, OfferCards, Audiences, ClosingCta) are small, self-contained components, so they can be swapped or reordered without touching the copy.

**Work.** The 51 imported projects stay in the repo, but only entries with `published: true` get a page or appear in a listing. Right now that's only `worship-online`. Flip the toggle in the CMS to publish another one.

## Editing content (Sveltia CMS)

Full guide: **[docs/CMS.md](docs/CMS.md)**. The short version:

- **Locally:** run `npm run dev`, open <http://localhost:4321/admin/index.html> in Chrome or Edge, then choose **Work with Local Repository** and pick this folder. Edits are written to disk; commit them with Git.
- **On the live site:** go to `/admin/` and choose **Sign In Using Access Token**, using a fine-grained GitHub token with Contents read/write on this repo. Each save commits to `main`, and Cloudflare Pages redeploys. For one-click GitHub sign-in, deploy the free [Sveltia CMS Authenticator](https://github.com/sveltia/sveltia-cms-auth) Worker and set `backend.base_url`.

`public/admin/config.yml` must stay in sync with `src/content.config.ts`. `npm run build` fails on content that doesn't match the schema.

## Design system: Lumos for Astro (trial)

This branch trials [Lumos for Astro](https://github.com/lumosframework/lumos-for-astro) by Timothy Ricks (MIT; see `THIRD-PARTY-NOTICES.md`), vendored from upstream commit `43e4d529`. The direction is minimal: lots of whitespace, a strong type scale, one accent color, and one primary button per page.

- **Tokens:** `src/styles/lumos/base.css` (fluid `clamp()` type and spacing scale, themes), `patterns.css`, and `utilities.css` are upstream files, kept unchanged so they can be upgraded. Paradox overrides live in `src/styles/paradox.css`: the system font stack, bold headings, a narrower page, and `--accent` (#3d7e68) as the only accent.
- **Primitives:** only the components the pages use are vendored: `Section`, `ButtonWrapper`, `Heading`, `Paragraph`, `RichText`, `Button`, `Img`, `Icon`, `Input`, `Select`, `Textarea`, `NavLink`, `SkipLink`.
- **Custom blocks:** `src/components/content/` (`HeroHome`, `ItemList`, `ContactForm`) plus `global/Nav` and `global/Footer`. Each owns its CSS in `<style is:global>` under `@layer components`, with root classes ending in `_wrap`, per Lumos's [`LUMOS.md`](https://github.com/lumosframework/lumos-for-astro/blob/main/LUMOS.md).

## Contact form

`/contact/` posts to a Cloudflare [Pages Function](https://developers.cloudflare.com/pages/functions/) at **`/api/contact`** (`functions/api/contact.ts`). The rest of the site stays static; Pages only runs the Function for that route. Everything here is meant to stay on free tiers: Pages Functions (100k requests/day), Turnstile (free), and Resend (free plan: 3,000 emails/month, 100/day).

What the Function does on each POST (JSON or form-encoded):

1. Rejects bodies over 32 KB and fails closed (503) if any required secret is missing.
2. Silently drops submissions that fill the hidden honeypot field (`website`).
3. Validates name, email, and message. Business name and offer are optional. The offer must look like an offer id or it's dropped.
4. Verifies the Turnstile token with `https://challenges.cloudflare.com/turnstile/v0/siteverify`, and requires `success`, action `contact`, and a hostname that matches an entry in `TURNSTILE_HOSTNAMES` or is a subdomain of one.
5. Sends the message with Resend, with `reply_to` set to the visitor's email.
6. Returns `{ "ok": true }` or `{ "ok": false, "error": "<code>" }`. The page turns the code into a message from `src/data/ui.json` (`contact.errors`). It never returns secrets or upstream error bodies. Without JavaScript, the form still works: the Function redirects back to `/contact/?status=sent` (or `?status=error&code=…`).

### Setup Ty needs to do

1. **Turnstile:** Cloudflare dashboard → Turnstile → Add widget. Add the site's hostnames (your custom domain, plus `paradox-cnd.pages.dev` if you want previews to work). Widget mode: Managed. Copy the site key and the secret key.
2. **Resend:** create a free account, verify the sending domain (DNS records), and create an API key with *Sending access*.
3. **Cloudflare Pages** → project → Settings → Variables and Secrets. Add these for **Production** (and **Preview** if previews should send mail):

| Name | Type | Value |
| --- | --- | --- |
| `PUBLIC_TURNSTILE_SITE_KEY` | Text | Turnstile site key. Read at **build** time, so redeploy after setting it. |
| `PUBLIC_CONTACT_FORM_ENABLED` | Text | `true` to show the form. Read at **build** time. Leave unset (off) until Resend works; `/contact/` then shows only the email link. |
| `TURNSTILE_SECRET` | Secret | Turnstile secret key |
| `TURNSTILE_HOSTNAMES` | Text | Comma-separated hostnames allowed to solve the widget, e.g. `example.com,paradox-cnd.pages.dev`. Each entry also allows its subdomains on a `.` boundary, so `paradox-cnd.pages.dev` covers every preview (`abc123.paradox-cnd.pages.dev`) but not `evil-paradox-cnd.pages.dev`. If unset, only the request's own hostname (and its subdomains) is accepted. |
| `RESEND_API_KEY` | Secret | Resend API key |
| `CONTACT_TO_EMAIL` | Text | Inbox for inquiries: `ty@paradoxcreative.com` (comma-separate for more than one) |
| `CONTACT_FROM_EMAIL` | Text | Optional. Sender on the verified domain, e.g. `Paradox website <hello@example.com>`. Defaults to `onboarding@resend.dev`, which Resend only delivers to the account owner. |

The form only renders when both `PUBLIC_TURNSTILE_SITE_KEY` is set and `PUBLIC_CONTACT_FORM_ENABLED=true` (including in `npm run dev`). Otherwise `/contact/` shows just the email link. If the server secrets are missing, the Function returns 503. Either way it fails closed.

### Local testing

`PUBLIC_CONTACT_FORM_ENABLED=true npm run dev` renders the form with Cloudflare's always-pass test site key, but the Astro dev server doesn't run Pages Functions. To test the whole round trip:

```sh
cp .dev.vars.example .dev.vars          # Turnstile test secret + your values
PUBLIC_CONTACT_FORM_ENABLED=true PUBLIC_TURNSTILE_SITE_KEY=1x00000000000000000000AA npm run build
npx wrangler pages dev dist             # http://localhost:8788/contact/
```

`.dev.vars` is gitignored. With a fake `RESEND_API_KEY`, everything up to the send step runs and the form shows the "could not be sent" error. Use a real key to receive mail. Cloudflare's [Turnstile test keys](https://developers.cloudflare.com/turnstile/troubleshooting/testing/) also include always-fail variants (`2x…`) for testing the rejection path.

## Deploying to Cloudflare Pages

Use Workers & Pages → Create → Pages → Connect to Git → `TyRichards/paradox`:

| Setting | Value |
| --- | --- |
| Production branch | `main` |
| Framework preset | Astro |
| Build command | `npm run build` |
| Build output directory | `dist` |
| Node version | from `.node-version` (no env var needed) |

### Site URL

Nothing in the code hardcodes a domain. `astro.config.mjs` sets Astro's `site` from **`PUBLIC_SITE_URL`**, falling back to `https://paradox-cnd.pages.dev`. That one value drives canonical links, Open Graph URLs, the sitemap, RSS, and `robots.txt` (generated by `src/pages/robots.txt.ts`). The CMS has no `site_url`, so it uses whatever origin it's opened on.

When a custom domain goes live, add `PUBLIC_SITE_URL` (e.g. `https://example.com`, no trailing slash) under Pages → Settings → Variables and Secrets for **Production**, then redeploy. Leave it unset for Preview so previews point at the pages.dev URL.

No adapter is needed because the site is fully static. Pages picks up `functions/` automatically. Set the [contact form variables](#setup-ty-needs-to-do) before expecting mail. Every PR gets a `*.pages.dev` preview URL. `public/_redirects` sends the old WordPress URLs (`/portfolio/*`, `/quote/`, etc.) and the retired `/services` and `/work/type/*` pages to the new routes. Old portfolio URLs for unpublished projects go to `/work/`.

## Project layout

```
public/
  admin/            Sveltia CMS (index.html + config.yml)
  _redirects        Cloudflare Pages redirects (legacy WordPress URLs)
  _headers          Cloudflare Pages headers
functions/
  api/contact.ts    Pages Function: Turnstile check + Resend email
src/
  content/          Markdown: pages/, offers/, work/, blog/
  content.config.ts Collection schemas (Zod)
  data/site.json    Site settings (CMS-editable)
  data/ui.json      Interface text (CMS-editable)
  components/       Lumos primitives (wrapper/, typography/, form/, media/, global/, Button)
  components/content/  Paradox blocks: HeroHome, ItemList, ContactForm
  layouts/          BaseLayout
  lib/              Content helpers, site settings loader
  pages/            Routes
  styles/global.css    CSS entry: Lumos layers + paradox.css brand tokens
docs/CMS.md         Editor/admin guide
IMPORT.md           Plan for the full WordPress (WXR) import
```

## Next steps

1. **Review the draft copy.** Home, Pricing, the three offers, About, Contact, Privacy, and `ui.json` are short honest drafts. Check the offer bullets especially, since they describe what each package includes.
2. **Choose which work to publish.** Toggle `published` on the strongest projects (see the shortlist in PR #1).
3. **Point a domain** at the Pages project when ready, and set `PUBLIC_SITE_URL` (see [Site URL](#site-url)).
4. **Set up CMS sign-in for editors.** Deploy Sveltia CMS Authenticator (free Worker), set `base_url`, and set `ALLOWED_DOMAINS`.
5. **Turn on the contact form.** Create the Turnstile widget and Resend key, then set the variables in [Contact form](#setup-ty-needs-to-do).
6. **Brand pass.** Add a social preview image, and optionally self-host a webfont (the 2015 site used Lato).
7. **Analytics, if wanted.** Cloudflare Web Analytics is free and cookieless.
