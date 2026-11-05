# WordPress import plan

The content in this repo is a **bootstrap** of the public structure of <https://paradoxcreative.com>. A WordPress export (WXR) isn't available yet. This document records what was bootstrapped, what's missing, and how to do the full import once the WXR exists.

## What's here now (bootstrapped 2026-11-05)

The live site's HTML and REST API sit behind a Cloudflare bot challenge, so the content was gathered from:

- The public WordPress sitemaps (`/wp-sitemap-*.xml`), which list every published post, page, portfolio item, and taxonomy term, with last-modified dates.
- Wayback Machine captures of the live pages from July 2026 (titles, descriptors, portfolio types, client/date meta, image URLs). Spot-checked against the live site.

| WordPress | Count | New location | Status |
| --- | --- | --- | --- |
| Portfolio items (`portfolio` post type) | 51 | `src/content/work/<slug>.md` | Title, one-line descriptor, portfolio types, client/date (where set), grid order, WordPress ID, original URL, thumbnail URL, and gallery image URLs. **No images or long-form copy imported.** |
| Portfolio types (`portfolio-type`) | 4: brand, web, print, app | `types` field on work entries; `src/content/services/*.md` | Service descriptions are labeled placeholders. |
| Pages | 4: Home, Portfolio, Quote, Privacy | `src/content/pages/` | Home intro and Quote (now Contact) copied verbatim. Portfolio became `/work/`. Privacy **not** copied (see below). |
| Posts | 1: “Hello world!” | — | Skipped: it's the WordPress default post. `src/content/blog/` holds one labeled placeholder post. |
| Categories | 1: Uncategorized | — | Skipped. |
| Author | Ty Richards | `src/data/site.json` (`owner`) | |

Nothing about client work was written beyond what the live site already says publicly. The About page, service blurbs, privacy policy, and blog post are flagged `placeholder: true` and show a visible notice on the page.

## Getting the export

1. In WP Admin, go to **Tools → Export → All content** and download the `.xml` (WXR). "All content" includes the `portfolio` custom post type and attachments.
2. Get the media files: either download `wp-content/uploads/` from the host (SFTP or a backup) or let the import script fetch each attachment by URL. The URLs are already recorded in `legacy.thumbnail` and `legacy.gallery`.
3. Put the export somewhere outside the repo, or under `import/` (add it to `.gitignore`; it contains emails and comment data).

## Field mapping

### Portfolio → `src/content/work/`

Match existing files by `legacy.wpId` (= `wp:post_id`) and fall back to the slug. **Update files in place; don't create duplicates.** Keep each filename (= `wp:post_name`) so `/portfolio/<slug>/` keeps redirecting to `/work/<slug>/`.

| WXR | Front matter |
| --- | --- |
| `title` | `title` |
| first paragraph of `content:encoded` | `summary` (already set; keep unless the WXR differs) |
| rest of `content:encoded` (HTML → Markdown) | body |
| `category domain="portfolio-type"` | `types` |
| postmeta `_tzp_portfolio_client` | `client` |
| postmeta `_tzp_portfolio_date` | `projectDate` (free text, e.g. “July 2012”) |
| postmeta `_tzp_portfolio_url` | add as a link in the body, or add a `projectUrl` field to the schema and CMS config |
| postmeta `_thumbnail_id` → attachment | `cover` (+ `coverAlt` from the attachment alt text) |
| postmeta `_tzp_gallery_images_ids` → attachments | gallery images (see “Images” below) |
| `wp:menu_order` | `order` (current values come from the live grid order) |
| `wp:post_id`, `link`, `wp:post_modified` | `legacy.wpId`, `legacy.url`, `legacy.modified` |

### Posts → `src/content/blog/`

| WXR | Front matter |
| --- | --- |
| `wp:post_name` | filename (`<slug>.md`) |
| `title` | `title` |
| `excerpt:encoded` | `description` |
| `wp:post_date_gmt` | `pubDate` |
| `wp:post_modified_gmt` | `updatedDate` (only if meaningfully later) |
| `category domain="post_tag"` / `domain="category"` | `tags` |
| `wp:status` ≠ `publish` | `draft: true` |
| `content:encoded` (HTML → Markdown) | body |
| `wp:post_id`, `link` | `legacy.wpId`, `legacy.url` |

Skip the default “Hello world!” post (ID 1) and its “Mr WordPress” comment. Comments aren't migrated: there's no comment system on the static site.

### Pages → `src/content/pages/`

Only pages that exist in `src/content/pages/` are mapped. A new WordPress page needs a new file there, a route in `src/pages/`, and an entry under the `pages` collection in `public/admin/config.yml`.

The 2015 **Privacy** page is generic boilerplate about orders, payments, and newsletters that the new site doesn't have. Rewrite it rather than import it.

## Images

The schema currently treats `cover` as a plain URL/path string served from `public/` (`/images/uploads/...`), which is what the CMS writes. For imported work images:

- **Simple:** save them to `public/images/work/<slug>/` and set `cover: /images/work/<slug>/<file>`. Astro serves them as-is.
- **Optimized (recommended for the full import):** store each work entry as a folder (`src/content/work/<slug>/index.md` plus images), switch `cover` to Astro's `image()` schema helper, and set `path: '{{slug}}/index'`, `media_folder: ''`, `public_folder: ''` on the `work` collection in the CMS config. That gives responsive, optimized images at build time. See the Sveltia [Astro guide](https://sveltiacms.app/en/docs/frameworks/astro).

To show gallery images, add a `gallery` field (a list of images) to the work schema, the CMS config, and `src/pages/work/[slug].astro`.

Strip WordPress size suffixes (`-960x624`, `-300x151`) and download the original files where possible.

## Suggested import script

Write it as a one-off Node script (e.g. `scripts/import-wxr.mjs`) using:

- [`fast-xml-parser`](https://www.npmjs.com/package/fast-xml-parser) to read the WXR
- [`turndown`](https://www.npmjs.com/package/turndown) to convert HTML to Markdown (strip `[zilla_*]` / `[gallery]` shortcodes first)
- [`yaml`](https://www.npmjs.com/package/yaml) with `lineWidth: 0` to write front matter in the same style Sveltia CMS writes, which keeps later CMS diffs small

Make it idempotent (re-runnable), and commit its output in reviewable chunks: work, then blog, then pages.

## URLs and redirects

`public/_redirects` already maps the known legacy URLs: `/portfolio/*`, `/portfolio-type/*`, `/quote/`, `/category/*`, `/author/*`, `/feed/`, and the hello-world permalink. If the import brings in more posts, WordPress date permalinks (`/YYYY/MM/DD/<slug>/`) need a rule such as:

```
/:year/:month/:day/:slug/   /blog/:slug/   301
```

Add it **after** the static rules. Then check the old sitemaps against the new site, e.g. with `curl -sI` against each `<loc>`.

## Done when

- [ ] Every URL in the old `wp-sitemap-*.xml` files returns 200 on the new site or redirects (301) to one that does
- [ ] Work entries have covers and write-ups; `placeholder` is off wherever real copy exists
- [ ] Real blog posts are in, and the placeholder post is deleted
- [ ] `npm run build` passes, and spot-checks of the CMS (`/admin/`) open imported entries without errors
