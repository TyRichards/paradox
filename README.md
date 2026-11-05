# Paradox

Static rebuild of [paradoxcreative.com](https://paradoxcreative.com) (Paradox Creative, Ty Richards). It replaces the 2015 WordPress site. The archived theme is at [TyRichards/paradox-2015](https://github.com/TyRichards/paradox-2015) for reference only.

## Stack

- **[Astro 7](https://astro.build/)** + **TypeScript** (strict), static output (`dist/`)
- **[Tailwind CSS 4](https://tailwindcss.com/)** via `@tailwindcss/vite` (Astro's recommended setup, added with `astro add tailwind`) and `@tailwindcss/typography` for Markdown. Theme tokens live in `src/styles/global.css` under `@theme`.
- **Markdown content collections** as the source of truth (`src/content/`, schemas in `src/content.config.ts`)
- **[Sveltia CMS](https://sveltiacms.app/en/)**: a Git-based editor over the same files, at `/admin/`. No database.
- **Cloudflare Pages** (free plan): static hosting, with `_redirects` / `_headers` in `public/`. No D1, R2, or paid Workers.

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
| `/contact/` | `src/content/pages/contact.md` + a non-functional form stub |
| `/privacy/` | `src/content/pages/privacy.md` (placeholder) |
| `/admin/` | Sveltia CMS (`public/admin/`) |

Site name, default meta, and nav are in `src/data/site.json`. All of it, including the site settings, can be edited in the CMS.

Content flagged `placeholder: true` shows a yellow notice on the page. Nothing marked that way should ship as-is.

## Editing content (Sveltia CMS)

Full guide: **[docs/CMS.md](docs/CMS.md)**. The short version:

- **Locally:** run `npm run dev`, open <http://localhost:4321/admin/index.html> in Chrome or Edge, then choose **Work with Local Repository** and pick this folder. Edits are written to disk; commit them with Git.
- **On the live site:** go to `/admin/` and choose **Sign In Using Access Token**, using a fine-grained GitHub token with Contents read/write on this repo. Each save commits to `main`, and Cloudflare Pages redeploys. For one-click GitHub sign-in, deploy the free [Sveltia CMS Authenticator](https://github.com/sveltia/sveltia-cms-auth) Worker and set `backend.base_url`.

`public/admin/config.yml` must stay in sync with `src/content.config.ts`. `npm run build` fails on content that doesn't match the schema.

## Deploying to Cloudflare Pages

Use Workers & Pages → Create → Pages → Connect to Git → `TyRichards/paradox`:

| Setting | Value |
| --- | --- |
| Production branch | `main` |
| Framework preset | Astro |
| Build command | `npm run build` |
| Build output directory | `dist` |
| Node version | from `.node-version` (no env var needed) |

No adapter is needed because the site is fully static. Every PR gets a `*.pages.dev` preview URL. `public/_redirects` sends the old WordPress URLs (`/portfolio/*`, `/quote/`, etc.) to the new routes.

## Project layout

```
public/
  admin/            Sveltia CMS (index.html + config.yml)
  _redirects        Cloudflare Pages redirects (legacy WordPress URLs)
  _headers          Cloudflare Pages headers
src/
  content/          Markdown: blog/, work/, services/, pages/
  content.config.ts Collection schemas (Zod)
  data/site.json    Site settings (CMS-editable)
  components/       Header, Footer, WorkCard, WorkGrid, ...
  layouts/          BaseLayout
  lib/              Content helpers, site settings loader
  pages/            Routes
  styles/global.css Tailwind entry + theme tokens
docs/CMS.md         Editor/admin guide
IMPORT.md           Plan for the full WordPress (WXR) import
```

## Next steps

1. **Full WordPress import.** Get a WXR export and follow [IMPORT.md](IMPORT.md): project images, write-ups, and any real posts. Then delete the placeholder blog post.
2. **Write the placeholder copy.** About, the four service descriptions, and a current privacy policy.
3. **Connect Cloudflare Pages** with the settings above. Point the domain there when ready; `site` in `astro.config.mjs` is already `https://paradoxcreative.com`.
4. **Set up CMS sign-in for editors.** Deploy Sveltia CMS Authenticator (free Worker), set `base_url`, and set `ALLOWED_DOMAINS`.
5. **Contact form.** The form is a stub. Free static-friendly options: a [Pages Function](https://developers.cloudflare.com/pages/functions/) on the free Workers quota that relays to email, or a hosted form endpoint (Formspree, Web3Forms, etc.). Add spam protection (Cloudflare Turnstile is free).
6. **Brand pass.** Update the tokens in `src/styles/global.css`, the favicon (still Astro's default), and the social preview image. Optionally self-host a webfont; the 2015 site used Lato.
7. **Analytics, if wanted.** Cloudflare Web Analytics is free and cookieless.
