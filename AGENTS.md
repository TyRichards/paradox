## Project

Static Astro 7 + Tailwind 4 site for Paradox Creative, deployed to Cloudflare Pages (free plan, static only). See README.md for the stack and layout, docs/CMS.md for Sveltia CMS, and IMPORT.md for the WordPress import plan.

- Content is Markdown in `src/content/`, with schemas in `src/content.config.ts`. Site settings are in `src/data/site.json` (schema in `src/lib/site.ts`).
- `public/admin/config.yml` (Sveltia CMS) mirrors those schemas. Change both together. Quote YAML flow-map values that contain commas.
- Don't invent client work or testimonials. Mark unverified copy with `placeholder: true`.
- No database CMS, no paid Cloudflare products (D1, R2, paid Workers), no SSR adapter. The only server code is the free-tier Pages Function in `functions/api/contact.ts`.
- UI uses Starwind UI (`src/components/starwind/`, add with `npx starwind@latest add <name>`). Styles and tokens live in `src/styles/starwind.css`; use semantic tokens (`primary`, `muted-foreground`, `border`) and the brand names (`teal`, `teal-light`, `ink`) rather than raw hex. Don't add React or shadcn/ui to this site.
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
