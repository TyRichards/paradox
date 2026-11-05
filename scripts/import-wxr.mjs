#!/usr/bin/env node
/**
 * Import paradoxcreative.com WXR export into Astro Markdown content collections.
 *
 * Usage:  node scripts/import-wxr.mjs import/paradox.wordpress.2026-11-05.000.xml
 *
 * See IMPORT.md for the field mapping this script follows.
 */

import fs from 'node:fs';
import path from 'node:path';
import https from 'node:https';
import http from 'node:http';
import { XMLParser } from 'fast-xml-parser';

const WXR = process.argv[2];
if (!WXR) { console.error('Usage: node scripts/import-wxr.mjs <wxr-file>'); process.exit(1); }

const REPO = path.resolve(import.meta.dirname, '..');
const WORK_DIR  = path.join(REPO, 'src/content/work');
const BLOG_DIR  = path.join(REPO, 'src/content/blog');
const PAGES_DIR = path.join(REPO, 'src/content/pages');
const IMG_DIR   = path.join(REPO, 'public/images/work');

// ── Parse WXR ──────────────────────────────────────────────────────────────────

const raw = fs.readFileSync(WXR, 'utf8');
const parser = new XMLParser({
  ignoreAttributes: false,
  attributeNamePrefix: '@_',
  isArray: (name) => ['item', 'category', 'wp:postmeta', 'wp:comment'].includes(name),
  tagValueProcessor: (_, val) => val,        // keep CDATA as-is
  cdataPropName: '__cdata',
  processEntities: true,
  htmlEntities: true,
});
const doc = parser.parse(raw);
const channel = doc.rss.channel;

// ── Build indices ──────────────────────────────────────────────────────────────

const items = channel.item;

function meta(item, key) {
  const pm = (item['wp:postmeta'] || []).find(m => cdata(m['wp:meta_key']) === key);
  return pm ? cdata(pm['wp:meta_value']) : '';
}

function cdata(v) {
  if (v == null) return '';
  if (typeof v === 'object' && '__cdata' in v) return v.__cdata;
  return String(v);
}

const attachments = new Map();
for (const it of items) {
  if (cdata(it['wp:post_type']) !== 'attachment') continue;
  const id = String(cdata(it['wp:post_id']));
  const url = cdata(it['wp:attachment_url']) || '';
  const alt = meta(it, '_wp_attachment_image_alt');
  attachments.set(id, { url, alt });
}

function attachmentUrl(id) { return attachments.get(String(id))?.url || ''; }
function attachmentAlt(id) { return attachments.get(String(id))?.alt || ''; }

// ── Image downloader ───────────────────────────────────────────────────────────

const downloaded = new Map();
const failedUrls = [];

async function downloadImage(url, destDir) {
  if (!url) return null;
  const basename = path.basename(new URL(url).pathname);
  // strip WP size suffixes like -960x624, -300x247 etc.
  const cleanName = basename.replace(/-\d+x\d+(\.\w+)$/, '$1');
  const dest = path.join(destDir, cleanName);
  if (downloaded.has(url)) return downloaded.get(url);
  if (fs.existsSync(dest)) {
    const rel = '/' + path.relative(path.join(REPO, 'public'), dest);
    downloaded.set(url, rel);
    return rel;
  }
  fs.mkdirSync(destDir, { recursive: true });
  try {
    const u = new URL(url.replace(/^http:/, 'https:'));
    const data = await fetchBuffer(u);
    fs.writeFileSync(dest, data);
    const rel = '/' + path.relative(path.join(REPO, 'public'), dest);
    downloaded.set(url, rel);
    console.log(`  ✓ ${rel}`);
    return rel;
  } catch (e) {
    failedUrls.push(url);
    downloaded.set(url, null);
    console.log(`  ✗ ${url}: ${e.message}`);
    return null;
  }
}

function fetchBuffer(url, redirects = 3) {
  return new Promise((resolve, reject) => {
    const mod = url.protocol === 'https:' ? https : http;
    mod.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' }, timeout: 30000 }, (res) => {
      if ([301, 302, 307, 308].includes(res.statusCode) && res.headers.location && redirects > 0) {
        return resolve(fetchBuffer(new URL(res.headers.location, url), redirects - 1));
      }
      if (res.statusCode !== 200) return reject(new Error(`HTTP ${res.statusCode}`));
      const chunks = [];
      res.on('data', (c) => chunks.push(c));
      res.on('end', () => resolve(Buffer.concat(chunks)));
      res.on('error', reject);
    }).on('error', reject);
  });
}

// ── HTML → Markdown ────────────────────────────────────────────────────────────

function htmlToMd(html) {
  if (!html) return '';
  let s = html;
  s = s.replace(/<!-- ?wp:[^>]+ ?-->/g, '');     // strip Gutenberg comments
  s = s.replace(/<br\s*\/?>/gi, '\n');
  s = s.replace(/<\/p>\s*<p[^>]*>/gi, '\n\n');
  s = s.replace(/<\/?p[^>]*>/gi, '');
  s = s.replace(/<em>(.*?)<\/em>/gi, '*$1*');
  s = s.replace(/<i>(.*?)<\/i>/gi, '*$1*');
  s = s.replace(/<strong>(.*?)<\/strong>/gi, '**$1**');
  s = s.replace(/<b>(.*?)<\/b>/gi, '**$1**');
  s = s.replace(/<a [^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/gi, '[$2]($1)');
  // strip remaining tags (images in content:encoded are handled separately)
  s = s.replace(/<img[^>]*>/gi, '');
  s = s.replace(/<[^>]+>/g, '');
  // decode entities
  s = s.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#8217;/g, "'").replace(/&#8220;/g, '"').replace(/&#8221;/g, '"').replace(/&nbsp;/g, ' ');
  return s.trim();
}

// ── YAML helpers ───────────────────────────────────────────────────────────────

function yamlStr(v) {
  if (v == null) return '""';
  const s = String(v);
  if (s === '' || /[:#\[\]{},&*?|>!'"%@`]/.test(s) || /^\s|\s$/.test(s) || /^(true|false|null|yes|no)$/i.test(s)) {
    return JSON.stringify(s);
  }
  return s;
}

function writeFrontMatter(fields) {
  let fm = '---\n';
  for (const [k, v] of Object.entries(fields)) {
    if (v === undefined) continue;
    if (Array.isArray(v)) {
      if (v.length === 0) continue;
      if (typeof v[0] === 'object') {
        fm += `${k}:\n`;
        for (const item of v) {
          const entries = Object.entries(item);
          fm += `  - ${entries[0][0]}: ${yamlStr(entries[0][1])}\n`;
          for (const [ek, ev] of entries.slice(1)) {
            fm += `    ${ek}: ${yamlStr(ev)}\n`;
          }
        }
      } else {
        fm += `${k}:\n`;
        for (const item of v) fm += `  - ${yamlStr(item)}\n`;
      }
    } else if (typeof v === 'object' && v !== null) {
      fm += `${k}:\n`;
      for (const [sk, sv] of Object.entries(v)) {
        if (sv === undefined) continue;
        if (Array.isArray(sv)) {
          if (sv.length === 0) continue;
          fm += `  ${sk}:\n`;
          for (const item of sv) fm += `    - ${yamlStr(item)}\n`;
        } else {
          fm += `  ${sk}: ${yamlStr(sv)}\n`;
        }
      }
    } else if (typeof v === 'boolean') {
      fm += `${k}: ${v}\n`;
    } else if (typeof v === 'number') {
      fm += `${k}: ${v}\n`;
    } else {
      fm += `${k}: ${yamlStr(v)}\n`;
    }
  }
  fm += '---\n';
  return fm;
}

// ── Existing file index (by wpId → filename) ──────────────────────────────────

function buildExistingIndex(dir) {
  const byId = new Map();
  const bySlug = new Map();
  if (!fs.existsSync(dir)) return { byId, bySlug };
  for (const f of fs.readdirSync(dir).filter(f => f.endsWith('.md'))) {
    const text = fs.readFileSync(path.join(dir, f), 'utf8');
    const m = text.match(/wpId:\s*(\d+)/);
    if (m) byId.set(Number(m[1]), f);
    bySlug.set(f.replace(/\.md$/, ''), f);
  }
  return { byId, bySlug };
}

// ── Import portfolio items ─────────────────────────────────────────────────────

async function importWork() {
  console.log('\n=== Importing portfolio → work ===');
  const idx = buildExistingIndex(WORK_DIR);
  let updated = 0, created = 0;

  for (const it of items) {
    if (cdata(it['wp:post_type']) !== 'portfolio') continue;
    if (cdata(it['wp:status']) !== 'publish') continue;

    const wpId = Number(cdata(it['wp:post_id']));
    const slug = cdata(it['wp:post_name']);
    const title = cdata(it.title);
    const rawOrder = Number(cdata(it['wp:menu_order']));
    const menuOrder = Number.isFinite(rawOrder) ? rawOrder : 999;
    const client = meta(it, '_tzp_portfolio_client') || undefined;
    const projectDate = meta(it, '_tzp_portfolio_date') || undefined;
    const projectUrl = meta(it, '_tzp_portfolio_url') || undefined;
    const thumbId = meta(it, '_thumbnail_id');
    const galleryIds = (meta(it, '_tzp_gallery_images_ids') || '')
      .split(',').map(s => s.trim()).filter(Boolean);

    const types = (it.category || [])
      .filter(c => c['@_domain'] === 'portfolio-type')
      .map(c => c['@_nicename']);

    const contentRaw = cdata(it['content:encoded']) || '';
    const bodyMd = htmlToMd(contentRaw);
    const lines = bodyMd.split('\n').map(l => l.trim()).filter(Boolean);
    const summary = lines[0] || undefined;
    const extraBody = lines.slice(1).join('\n\n').trim() || '';

    // Determine target filename
    const existingFile = idx.byId.get(wpId) || idx.bySlug.get(slug);
    const filename = existingFile || `${slug}.md`;
    const destDir = path.join(IMG_DIR, slug);

    console.log(`\n[${wpId}] ${slug}`);

    // Download cover (thumbnail)
    let cover, coverAlt;
    if (thumbId) {
      const thumbUrl = attachmentUrl(thumbId);
      if (thumbUrl) {
        const localPath = await downloadImage(thumbUrl, destDir);
        if (localPath) {
          cover = localPath;
          coverAlt = attachmentAlt(thumbId) || undefined;
        }
      }
    }

    // Download gallery images
    const gallery = [];
    for (const gid of galleryIds) {
      const gUrl = attachmentUrl(gid);
      if (gUrl) {
        const localPath = await downloadImage(gUrl, destDir);
        if (localPath) gallery.push(localPath);
      }
    }

    // Compute order from menu_order: WP stores higher = earlier in grid for this theme.
    // The original bootstrap used 1-based order matching the live grid. WXR menu_order:
    // Spotio=1, Cross Timbers=2, Sullivan=3, ... Victory w/ Valvoline=0, and brand-only up to 50.
    // Use menu_order directly since it matches the live display order.
    const order = menuOrder;

    const fm = {
      title,
      summary,
      types,
      client,
      projectDate,
      order,
      cover,
      coverAlt,
      gallery: gallery.length > 0 ? gallery : undefined,
      featured: false,
      placeholder: false,
      legacy: {
        wpId,
        url: `https://paradoxcreative.com/portfolio/${slug}/`,
        modified: cdata(it['wp:post_modified_gmt'])?.split(' ')[0],
        thumbnail: attachmentUrl(thumbId) || undefined,
        gallery: galleryIds.map(id => attachmentUrl(id)).filter(Boolean),
      },
    };

    const md = writeFrontMatter(fm) + (extraBody ? '\n' + extraBody + '\n' : '');
    fs.writeFileSync(path.join(WORK_DIR, filename), md);
    if (existingFile) updated++; else created++;
  }
  console.log(`\nWork: ${updated} updated, ${created} created`);
}

// ── Import pages ───────────────────────────────────────────────────────────────

async function importPages() {
  console.log('\n=== Importing pages ===');

  for (const it of items) {
    if (cdata(it['wp:post_type']) !== 'page') continue;
    const wpId = Number(cdata(it['wp:post_id']));
    const slug = cdata(it['wp:post_name']);
    const title = cdata(it.title);
    const contentRaw = cdata(it['content:encoded']) || '';

    // Page ID 2 (slug "contact") is actually the Home page
    if (wpId === 2) {
      console.log(`\n[${wpId}] Home page (WP slug "${slug}")`);
      const bodyMd = htmlToMd(contentRaw);
      // The home page content is already pretty good. Extract the core paragraph.
      // Strip the headshot image reference and button links from the MD since they're in the template.
      let cleaned = bodyMd
        .replace(/\[.*?Let's Chat.*?\]\([^)]*\)/g, '')
        .replace(/\[.*?See Portfolio.*?\]\([^)]*\)/g, '')
        .replace(/\s{2,}/g, ' ')
        .trim();
      // Find the main paragraph: starts with "You want to grow..."
      const mainStart = cleaned.indexOf('You want to grow');
      if (mainStart >= 0) cleaned = cleaned.substring(mainStart).trim();
      if (cleaned) {
        const pagePath = path.join(PAGES_DIR, 'home.md');
        const existing = fs.readFileSync(pagePath, 'utf8');
        const fmMatch = existing.match(/^---\n([\s\S]*?)\n---\n/);
        if (fmMatch) {
          fs.writeFileSync(pagePath, fmMatch[0] + '\n' + cleaned + '\n');
          console.log('  Updated home body from WXR');
        }
      }
      continue;
    }

    // Page ID 67 is the Quote/Contact page
    if (wpId === 67) {
      console.log(`\n[${wpId}] Quote → Contact`);
      const bodyMd = htmlToMd(contentRaw);
      if (bodyMd) {
        const pagePath = path.join(PAGES_DIR, 'contact.md');
        const existing = fs.readFileSync(pagePath, 'utf8');
        const fmMatch = existing.match(/^---\n([\s\S]*?)\n---\n/);
        if (fmMatch) {
          fs.writeFileSync(pagePath, fmMatch[0] + '\n' + bodyMd + '\n');
          console.log('  Updated contact body from WXR');
        }
      }
      continue;
    }

    // Page ID 41 (privacy) — skip per instructions
    if (wpId === 41) {
      console.log(`\n[${wpId}] Privacy — skipped (rewrite placeholder per IMPORT.md)`);
      continue;
    }

    // Page ID 16 (portfolio) — skip, content is empty, now /work/
    if (wpId === 16) {
      console.log(`\n[${wpId}] Portfolio — skipped (empty, now /work/)`);
      continue;
    }

    console.log(`\n[${wpId}] ${slug} — not mapped, skipping`);
  }
}

// ── Main ───────────────────────────────────────────────────────────────────────

async function main() {
  console.log(`Importing from: ${WXR}`);
  console.log(`Attachments indexed: ${attachments.size}`);

  await importWork();
  await importPages();

  console.log('\n=== Summary ===');
  if (failedUrls.length) {
    console.log(`\nFailed downloads (${failedUrls.length}):`);
    for (const u of failedUrls) console.log(`  ${u}`);
  } else {
    console.log('All image downloads succeeded.');
  }
  console.log('\nDone.');
}

main().catch(e => { console.error(e); process.exit(1); });
