# WordPress import plan

This document records what was imported from the WordPress export (WXR), what's still outstanding, and how to finish the migration.

## Import status (updated 2026-11-05)

The WXR export (`paradox.wordpress.2026-11-05.000.xml`, WordPress 7.1.4 / WP Engine) was imported with `node scripts/import-wxr.mjs`. The raw WXR is under `import/` (gitignored).

| WordPress content | Count | New location | Status |
| --- | --- | --- | --- |
| Portfolio items (`portfolio`) | 51 | `src/content/work/<slug>.md` | **Imported.** Title, summary (from `content:encoded`), types, client, projectDate, menu_order, cover image, gallery images. 134 images downloaded to `public/images/work/<slug>/`. |
| Portfolio types (`portfolio-type`) | 4 | `types` field + `src/content/services/*.md` | Types mapped. Service descriptions remain labeled placeholders. |
| Home page (WP ID 2) | 1 | `src/content/pages/home.md` | **Imported.** Body text from WXR. |
| Quote page (WP ID 67) | 1 | `src/content/pages/contact.md` | **Imported.** Body text from WXR. |
| Portfolio page (WP ID 16) | 1 | `/work/` route | Skipped (empty in WXR; the route already exists). |
| Privacy page (WP ID 41) | 1 | `src/content/pages/privacy.md` | **Skipped.** Generic 2015 boilerplate about orders/payments that doesn't match the static site. Left as a rewrite placeholder. |
| Posts | 1 | — | **Skipped.** Only post is the default "Hello world!" (ID 1). The placeholder blog post remains. |
| Comments | ~1 | — | Skipped. No comment system on the static site. |
| Attachments | 158 | `public/images/work/` | **134 project images downloaded** (covers + galleries). 24 remaining attachments are site chrome (logos, favicons, headshots) not used in content. |
| Categories | 1 | — | Skipped (only "Uncategorized"). |
| Nav menus | 2 | `src/data/site.json` | Already defined; WXR nav items not imported (hardcoded in site.json). |
| Author | 1 | `src/data/site.json` (`owner`) | Already set. |

### What's still outstanding

- [ ] **Privacy policy:** Rewrite `src/content/pages/privacy.md` to match what the static site actually collects.
- [ ] **About page:** Write real copy for `src/content/pages/about.md`.
- [ ] **Service descriptions:** Write real copy for the four `src/content/services/*.md` files.
- [ ] **Blog:** Write real posts (or set `draft: true` on the placeholder and leave it). Delete `src/content/blog/welcome-to-the-new-paradox.md` before launch.
- [ ] **Image optimization:** The current images are served as-is from `public/`. For production, consider moving to entry-relative folders with Astro's `image()` schema helper for responsive/optimized output. See the "Images (future optimization)" section below.
- [ ] **`_tzp_portfolio_url`:** Not present in any WXR portfolio item. No `projectUrl` field was added.

## Source data

### Bootstrap (2026-11-05, before WXR)

The live site's HTML and REST API sit behind a Cloudflare bot challenge, so the initial content was gathered from:

- The public WordPress sitemaps (`/wp-sitemap-*.xml`)
- Wayback Machine captures of the live pages from July 2026

### WXR import (2026-11-05)

Full "All content" export from `wp export` on WP Engine (`paradoxsites`), WordPress 7.1.4. The raw file is in `import/` (gitignored; contains the author's email address).

## Field mapping

### Portfolio → `src/content/work/`

Files matched by `legacy.wpId` (= `wp:post_id`), falling back to slug. Updated in place; no duplicates.

| WXR | Front matter | Notes |
| --- | --- | --- |
| `title` | `title` | |
| first paragraph of `content:encoded` | `summary` | HTML→Markdown; kept the WXR version |
| rest of `content:encoded` | body | Rare: only a few items had body text beyond the summary |
| `category domain="portfolio-type"` | `types` | |
| postmeta `_tzp_portfolio_client` | `client` | 10 items have this |
| postmeta `_tzp_portfolio_date` | `projectDate` | 10 items; free text like "July 2012" |
| postmeta `_tzp_portfolio_url` | — | **Not present** in any WXR item |
| postmeta `_thumbnail_id` → attachment | `cover`, `coverAlt` | Downloaded to `public/images/work/<slug>/` |
| postmeta `_tzp_gallery_images_ids` → attachments | `gallery` (local paths) | Downloaded; also kept as `legacy.gallery` (original URLs) |
| `wp:menu_order` | `order` | 0 = first in grid (Victory with Valvoline) |
| `wp:post_id`, `link`, `wp:post_modified` | `legacy.wpId`, `legacy.url`, `legacy.modified` | |

### Posts → `src/content/blog/`

Only one published post in the WXR (ID 1, "Hello world!"), which was skipped per plan.

### Pages → `src/content/pages/`

| WP ID | WP slug | Astro page | Status |
| --- | --- | --- | --- |
| 2 | `contact` | `home.md` | **Imported** (WP ID 2's `title` is "Home" and its slug is "contact" due to WP permalink history) |
| 16 | `portfolio` | `/work/` route | Skipped (empty) |
| 41 | `privacy` | `privacy.md` | Skipped (rewrite placeholder) |
| 67 | `quote` | `contact.md` | **Imported** |

## Images

Covers and gallery images are served as static files from `public/images/work/<slug>/`. WordPress size suffixes (e.g. `-960x624`) were stripped when downloading.

### Future optimization

For responsive/optimized images at build time:

1. Move each work entry to a folder: `src/content/work/<slug>/index.md` with images alongside.
2. Switch `cover` to Astro's `image()` schema helper in `src/content.config.ts`.
3. Set `path: '{{slug}}/index'`, `media_folder: ''`, `public_folder: ''` on the `work` collection in `public/admin/config.yml`.

See the [Sveltia Astro guide](https://sveltiacms.app/en/docs/frameworks/astro) for details.

## Import script

The import script lives at `scripts/import-wxr.mjs`. It uses `fast-xml-parser` (added as a dev dependency). It's idempotent: re-running it overwrites the same files with the same content.

```sh
node scripts/import-wxr.mjs import/paradox.wordpress.2026-11-05.000.xml
```

## URLs and redirects

`public/_redirects` maps the known legacy URLs: `/portfolio/*`, `/portfolio-type/*`, `/quote/`, `/category/*`, `/author/*`, `/feed/`, and the hello-world permalink. Since the only real post was skipped, no date-based permalink rules were added.

## Checklist

- [x] `npm run build` passes (64 pages, 0 errors)
- [x] Work entries have covers and galleries from the WXR
- [x] `placeholder: false` on all work entries
- [x] Home and contact page bodies from WXR
- [ ] Privacy policy rewritten
- [ ] About page written
- [ ] Service descriptions written
- [ ] Real blog posts added
- [ ] Image optimization (entry-relative folders + `image()`)
