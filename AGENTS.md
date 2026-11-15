## Project

Static Astro 7 + Lumos for Astro (plain CSS) site for Paradox (business process & automation agency), deployed to Cloudflare Pages (free plan, static only). See README.md for the stack and layout, docs/CMS.md for Sveltia CMS, and IMPORT.md for the WordPress import plan.

- Content is Markdown in `src/content/`, with schemas in `src/content.config.ts`. Site settings are in `src/data/site.json` (schema in `src/lib/site.ts`); interface text is in `src/data/ui.json` (schema in `src/lib/ui.ts`). Keep user-facing strings in content files, not components.
- `public/admin/config.yml` (Sveltia CMS) mirrors those schemas. Change both together. Quote YAML flow-map values that contain commas.
- Don't invent client work, testimonials, or stats. Work entries stay hidden unless `published: true`.
- No hardcoded domain: the site URL comes from `PUBLIC_SITE_URL` (see README).
- No database CMS, no paid Cloudflare products (D1, R2, paid Workers), no SSR adapter. The only server code is the free-tier Pages Function in `functions/api/contact.ts`.
- UI uses Lumos for Astro (MIT, vendored; see README and `THIRD-PARTY-NOTICES.md`). Keep `src/styles/lumos/*` unchanged and put brand overrides in `src/styles/paradox.css`. Keep it minimal: one accent (`--accent`), one primary button per page, no decorative icons, badges, gradients, or card grids. Custom CSS lives only in components (root class ends in `_wrap`). No Tailwind, React, or shadcn/ui.
- Run `npm run check` and `npm run build` before committing.

## Development

When starting the dev server, use background mode:

```
astro dev --background
```

Manage the background server with `astro dev stop`, `astro dev status`, and `astro dev logs`.

Open the CMS at `/admin/index.html` on the dev server (Astro doesn't serve `public/admin/` as a folder index).

## Documentation

Full documentation: https://docs.astro.build

Consult these guides before working on related tasks:

- [Adding pages, dynamic routes, or middleware](https://docs.astro.build/en/guides/routing/)
- [Working with Astro components](https://docs.astro.build/en/basics/astro-components/)
- [Adding or managing content](https://docs.astro.build/en/guides/content-collections/)
- [Adding styles or using Tailwind](https://docs.astro.build/en/guides/styling/)
- [Sveltia CMS + Astro](https://sveltiacms.app/en/docs/frameworks/astro)
