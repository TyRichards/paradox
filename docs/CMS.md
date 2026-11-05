# Editing content with Sveltia CMS

[Sveltia CMS](https://sveltiacms.app/en/) is a Git-based CMS. It has no database or server of its own: it reads and writes the Markdown/JSON files in this repository. Every save is a Git commit (or, when working locally, a file change you commit yourself). Then Cloudflare Pages rebuilds the site.

- Admin UI: `/admin/` on the deployed site (`/admin/index.html` on the dev server)
- Config: [`public/admin/config.yml`](../public/admin/config.yml)
- Loader page: [`public/admin/index.html`](../public/admin/index.html). It loads the CMS from the unpkg CDN, unpinned, which is what Sveltia recommends so it keeps itself updated.

## What you can edit

| CMS collection | Files | Shows up at |
| --- | --- | --- |
| Blog | `src/content/blog/*.md` | `/blog/`, `/blog/<slug>/`, `/rss.xml` |
| Work | `src/content/work/*.md` | `/work/`, `/work/<slug>/`, `/work/type/<type>/`, home page |
| Services | `src/content/services/*.md` | `/services/`, home page |
| Pages | `src/content/pages/{home,about,services,contact,privacy}.md` | the matching page |
| Site settings | `src/data/site.json` | site name, default title/description, main nav |

The read-only **WordPress origin** group on blog and work entries records where an entry came from on the old site. Leave it alone; the import uses it (see [`IMPORT.md`](../IMPORT.md)).

**Placeholder** toggles show a yellow "placeholder content" notice on the page. Turn the toggle off once the real copy is in.

## Option 1: edit locally (no sign-in)

Requires Chrome, Edge, or another Chromium browser (it uses the File System Access API).

1. `npm run dev`
2. Open <http://localhost:4321/admin/index.html>. Include `index.html`, because Astro's dev server doesn't serve `public/admin/` as a folder index.
3. Click **Work with Local Repository** and pick the repository's root folder.
4. Edit and save. Files change on disk and the dev site hot-reloads.
5. Review with `git diff`, then commit and push. (Optional: Settings → Developer → *Commit Changes to Git* makes the CMS commit each save to your current branch. It needs `git config --local user.name/user.email`.)

## Option 2: edit the live site

On `https://<your-site>/admin/`:

- **Sign In Using Access Token** works right away. Create a fine-grained GitHub token for `TyRichards/paradox` with **Contents: read and write**. The sign-in dialog links to a pre-filled token page. The token stays in your browser.
- **Sign In with GitHub** (one click, better for non-technical editors) needs a small OAuth helper. Use [Sveltia CMS Authenticator](https://github.com/sveltia/sveltia-cms-auth) on the **free** Cloudflare Workers plan:
  1. Deploy it with the "Deploy to Cloudflare Workers" button in its README. Note the Worker URL (`https://sveltia-cms-auth.<subdomain>.workers.dev`).
  2. [Register a GitHub OAuth app](https://github.com/settings/applications/new) with callback URL `<WORKER_URL>/callback`, then generate a client secret.
  3. In the Worker's settings, add the variables `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET` (encrypted), and `ALLOWED_DOMAINS` (e.g. `paradoxcreative.com, *.paradoxcreative.com, *.pages.dev`).
  4. Uncomment `base_url` under `backend` in `public/admin/config.yml`, set it to the Worker URL, then commit.

Editors need write access to the GitHub repo. Saves commit straight to `main`. That works with branch protection off; if you turn on "require pull request", switch the CMS to the [editorial workflow](https://sveltiacms.app/en/docs/workflows/editorial) (`publish_mode: editorial_workflow`).

## Changing the content model

The CMS config and the Astro schemas describe the same fields twice, and they must agree:

- Content collections: `src/content.config.ts` (Zod)
- Site settings: `src/lib/site.ts` (Zod)
- CMS fields: `public/admin/config.yml`

When you add or rename a field, update both, then run `npm run build`. The build fails if any file doesn't match its schema. `output.omit_empty_optional_fields: true` in the CMS config keeps empty optional fields out of front matter, so Zod `.optional()` accepts them.

Media uploaded through the CMS goes to `public/images/uploads/` and is served as-is from `/images/uploads/…`.

## Troubleshooting

- **Blank admin page or config error.** Validate `config.yml` against the [JSON schema](https://unpkg.com/@sveltia/cms/schema/sveltia-cms.json). The VS Code YAML extension does this automatically through the `$schema` comment on line 1. In YAML flow maps (`{ … }`), quote any value that contains a comma.
- **"Not a repository root directory".** Pick the folder that contains `.git`.
- **Build fails after a CMS save.** The saved front matter doesn't match `src/content.config.ts`. The build error names the file and field.
