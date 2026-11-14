// @ts-check
import { readdirSync } from 'node:fs';
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwindcss from '@tailwindcss/vite';
import { loadEnv } from 'vite';

// The site URL drives canonical links, Open Graph URLs, the sitemap, RSS, and robots.txt.
// Set PUBLIC_SITE_URL (Cloudflare Pages → Settings → Variables) once a custom domain is live.
const env = loadEnv(process.env.NODE_ENV ?? 'production', process.cwd(), '');
const site = env.PUBLIC_SITE_URL || 'https://paradox-cnd.pages.dev';

// The blog stays out of the nav and sitemap until it has a post.
const hasPosts = readdirSync('./src/content/blog').some((f) => f.endsWith('.md'));

// https://astro.build/config
export default defineConfig({
  site,
  output: 'static',
  integrations: [
    sitemap({
      filter: (page) => !page.includes('/admin/') && (hasPosts || !new URL(page).pathname.startsWith('/blog')),
    }),
  ],
  vite: {
    plugins: [tailwindcss()],
  },
});
