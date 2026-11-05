# Paradox

Static rebuild of [paradoxcreative.com](https://paradoxcreative.com) (Paradox Creative, Ty Richards). It replaces the 2015 WordPress site. The archived theme is at [TyRichards/paradox-2015](https://github.com/TyRichards/paradox-2015) for reference only.

## Stack

- **[Astro 7](https://astro.build/)** + **TypeScript** (strict), static output (`dist/`)
- **[Tailwind CSS 4](https://tailwindcss.com/)** via `@tailwindcss/vite` (Astro's recommended setup, added with `astro add tailwind`).
- **[Starwind UI](https://starwind.dev/)** components (Astro-native, Tailwind 4) in `src/components/starwind/`. The single stylesheet is `src/styles/starwind.css`: Paradox brand colors plus Starwind's theme tokens mapped onto them.
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
| `/` | `src/content/pages/home.md` + featured work, services, latest posts |
| `/about/` | `src/content/pages/about.md` (placeholder) |
| `/services/` | `src/content/pages/services.md` + `src/content/services/*.md` |
| `/work/`, `/work/type/<brand\|web\|print\|app>/`, `/work/<slug>/` | `src/content/work/*.md` (51 projects from the old portfolio) |
| `/blog/`, `/blog/<slug>/`, `/rss.xml` | `src/content/blog/*.md` |
| `/contact/` | `src/content/pages/contact.md` + the contact form (posts to `/api/contact`, see [Contact form](#contact-form)) |
| `/privacy/` | `src/content/pages/privacy.md` (placeholder) |
| `/admin/` | Sveltia CMS (`public/admin/`) |

Site name, default meta, and nav are in `src/data/site.json`. All of it, including the site settings, can be edited in the CMS.

Content flagged `placeholder: true` shows a yellow notice on the page. Nothing marked that way should ship as-is.

## Editing content (Sveltia CMS)

Full guide: **[docs/CMS.md](docs/CMS.md)**. The short version:

- **Locally:** run `npm run dev`, open <http://localhost:4321/admin/index.html> in Chrome or Edge, then choose **Work with Local Repository** and pick this folder. Edits are written to disk; commit them with Git.
- **On the live site:** go to `/admin/` and choose **Sign In Using Access Token**, using a fine-grained GitHub token with Contents read/write on this repo. Each save commits to `main`, and Cloudflare Pages redeploys. For one-click GitHub sign-in, deploy the free [Sveltia CMS Authenticator](https://github.com/sveltia/sveltia-cms-auth) Worker and set `backend.base_url`.

`public/admin/config.yml` must stay in sync with `src/content.config.ts`. `npm run build` fails on content that doesn't match the schema.

## Design system: Starwind UI

[Starwind UI](https://starwind.dev/) is the default design system for Paradox static sites. It's Astro-native (no React), built on Tailwind 4, and its components are copied into the repo, so they can be edited like any other source file.

- Components live in `src/components/starwind/` and are imported through the `@/` alias, e.g. `import { Button } from '@/components/starwind/button'`.
- Installed so far: alert, avatar, badge, button, card, input, label, prose, separator, sheet (mobile menu), spinner, textarea. Add more with `npx starwind@latest add <name>`, and update them with `npx starwind@latest update`.
- **Theming:** `src/styles/starwind.css` defines the Paradox palette (`ink`, `paper`, `mist`, `line`, `teal`, `teal-light`, `teal-ink`, `highlight`) and maps Starwind's semantic tokens onto it. `primary` is `teal-ink` (#3d7e68) rather than the logo teal (#51a589), because white text on the logo teal fails WCAG AA contrast. Use the brand names (e.g. `bg-teal-light text-ink`) for brand moments like the hero, and the semantic names (`bg-primary`, `text-muted-foreground`, `border-border`) everywhere else.
- Markdown bodies render through Starwind's `Prose` component (replaces `@tailwindcss/typography`).

**Backup:** [shadcn/ui](https://ui.shadcn.com/) is the fallback for full apps, or for sites that need its deeper React component set (data tables, complex forms, command menus). It needs React islands, so it isn't installed here.

## Contact form

`/contact/` posts to a Cloudflare [Pages Function](https://developers.cloudflare.com/pages/functions/) at **`/api/contact`** (`functions/api/contact.ts`). The rest of the site stays static; Pages only runs the Function for that route. Everything here is meant to stay on free tiers: Pages Functions (100k requests/day), Turnstile (free), and Resend (free plan: 3,000 emails/month, 100/day).

What the Function does on each POST (JSON or form-encoded):

1. Rejects bodies over 32 KB and fails closed (503) if any required secret is missing.
2. Silently drops submissions that fill the hidden honeypot field (`company`).
3. Validates name, email, and project details (budget is optional).
4. Verifies the Turnstile token with `https://challenges.cloudflare.com/turnstile/v0/siteverify`, and requires `success`, action `contact`, and a hostname from `TURNSTILE_HOSTNAMES`.
5. Sends the message with Resend, with `reply_to` set to the visitor's email.
6. Returns `{ "ok": true }` or `{ "ok": false, "error": "…" }`. It never returns secrets or upstream error bodies. Without JavaScript, the form still works: the Function redirects back to `/contact/?status=sent` (or `=error`).

### Setup Ty needs to do

1. **Turnstile:** Cloudflare dashboard → Turnstile → Add widget. Add the hostnames (`paradoxcreative.com`, `www.paradoxcreative.com`, and the `*.pages.dev` project hostname if you want previews to work). Widget mode: Managed. Copy the site key and the secret key.
2. **Resend:** create a free account, verify the `paradoxcreative.com` sending domain (DNS records), and create an API key with *Sending access*.
3. **Cloudflare Pages** → project → Settings → Variables and Secrets. Add these for **Production** (and **Preview** if previews should send mail):

| Name | Type | Value |
| --- | --- | --- |
| `PUBLIC_TURNSTILE_SITE_KEY` | Text | Turnstile site key. Read at **build** time, so redeploy after setting it. |
| `TURNSTILE_SECRET` | Secret | Turnstile secret key |
| `TURNSTILE_HOSTNAMES` | Text | Comma-separated hostnames allowed to solve the widget, e.g. `paradoxcreative.com,www.paradoxcreative.com,paradox.pages.dev`. If unset, only the request's own hostname is accepted. |
| `RESEND_API_KEY` | Secret | Resend API key |
| `CONTACT_TO_EMAIL` | Text | Inbox for inquiries (comma-separate for more than one) |
| `CONTACT_FROM_EMAIL` | Text | Optional. Sender on the verified domain, e.g. `Paradox website <hello@paradoxcreative.com>`. Defaults to `onboarding@resend.dev`, which Resend only delivers to the account owner. |

If `PUBLIC_TURNSTILE_SITE_KEY` is missing from a production build, the form renders disabled with a "use email instead" notice. If the server secrets are missing, the Function returns 503. Either way it fails closed.

### Local testing

`npm run dev` renders the form with Cloudflare's always-pass test site key, but the Astro dev server doesn't run Pages Functions. To test the whole round trip:

```sh
cp .dev.vars.example .dev.vars          # Turnstile test secret + your values
PUBLIC_TURNSTILE_SITE_KEY=1x00000000000000000000AA npm run build
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

No adapter is needed because the site is fully static. Pages picks up `functions/` automatically. Set the [contact form variables](#setup-ty-needs-to-do) before expecting mail. Every PR gets a `*.pages.dev` preview URL. `public/_redirects` sends the old WordPress URLs (`/portfolio/*`, `/quote/`, etc.) to the new routes.

## Project layout

```
public/
  admin/            Sveltia CMS (index.html + config.yml)
  _redirects        Cloudflare Pages redirects (legacy WordPress URLs)
  _headers          Cloudflare Pages headers
functions/
  api/contact.ts    Pages Function: Turnstile check + Resend email
src/
  content/          Markdown: blog/, work/, services/, pages/
  content.config.ts Collection schemas (Zod)
  data/site.json    Site settings (CMS-editable)
  components/       Header, Footer, WorkCard, WorkGrid, ...
  components/starwind/  Starwind UI components (Button, Card, Input, ...)
  layouts/          BaseLayout
  lib/              Content helpers, site settings loader
  pages/            Routes
  styles/starwind.css  Tailwind entry, Paradox palette, Starwind theme tokens
docs/CMS.md         Editor/admin guide
IMPORT.md           Plan for the full WordPress (WXR) import
```

## Next steps

1. **Finish the WordPress leftovers.** See the outstanding list in [IMPORT.md](IMPORT.md), and delete the placeholder blog post once real posts exist.
2. **Write the placeholder copy.** About, the four service descriptions, and a current privacy policy.
3. **Connect Cloudflare Pages** with the settings above. Point the domain there when ready; `site` in `astro.config.mjs` is already `https://paradoxcreative.com`.
4. **Set up CMS sign-in for editors.** Deploy Sveltia CMS Authenticator (free Worker), set `base_url`, and set `ALLOWED_DOMAINS`.
5. **Turn on the contact form.** Create the Turnstile widget and Resend key, then set the variables in [Contact form](#setup-ty-needs-to-do).
6. **Brand pass.** Add a social preview image, and optionally self-host a webfont (the 2015 site used Lato).
7. **Analytics, if wanted.** Cloudflare Web Analytics is free and cookieless.
