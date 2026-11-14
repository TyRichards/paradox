import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { z } from 'astro/zod';

// Keep these schemas in sync with public/admin/config.yml (Sveltia CMS).

export const WORK_TYPES = ['brand', 'web', 'print', 'app'] as const;

const link = z.object({
  label: z.string(),
  href: z.string(),
});

const pages = defineCollection({
  loader: glob({ pattern: '*.md', base: './src/content/pages' }),
  schema: z.object({
    title: z.string(),
    description: z.string().optional(),
    eyebrow: z.string().optional(),
    heading: z.string().optional(),
    lede: z.string().optional(),
    portrait: z.object({ src: z.string(), alt: z.string() }).optional(),
    ctas: z.array(link).optional(),
    audiences: z
      .object({
        heading: z.string(),
        items: z.array(z.object({ title: z.string(), body: z.string() })),
      })
      .optional(),
    offersSection: z.object({ heading: z.string(), intro: z.string().optional(), linkLabel: z.string() }).optional(),
    workSection: z.object({ heading: z.string(), linkLabel: z.string() }).optional(),
    closing: z.object({ heading: z.string(), text: z.string(), cta: link }).optional(),
  }),
});

const offers = defineCollection({
  loader: glob({ pattern: '*.md', base: './src/content/offers' }),
  schema: z.object({
    title: z.string(),
    price: z.string(),
    cadence: z.string().optional(),
    summary: z.string(),
    bullets: z.array(z.string()).max(5).default([]),
    ctaLabel: z.string(),
    featured: z.boolean().default(false),
    order: z.number().int().default(999),
  }),
});

const blog = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/blog' }),
  schema: z.object({
    title: z.string(),
    description: z.string().optional(),
    pubDate: z.coerce.date(),
    updatedDate: z.coerce.date().optional(),
    cover: z.string().optional(),
    coverAlt: z.string().optional(),
    tags: z.array(z.string()).default([]),
    draft: z.boolean().default(false),
    legacy: z
      .object({
        wpId: z.number().int().optional(),
        url: z.url().optional(),
      })
      .optional(),
  }),
});

const work = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/work' }),
  schema: z.object({
    title: z.string(),
    summary: z.string().optional(),
    published: z.boolean().default(false),
    types: z.array(z.enum(WORK_TYPES)).default([]),
    client: z.string().optional(),
    projectDate: z.string().optional(),
    projectUrl: z.url().optional(),
    order: z.number().int().default(999),
    cover: z.string().optional(),
    coverAlt: z.string().optional(),
    gallery: z.array(z.string()).default([]),
    featured: z.boolean().default(false),
    legacy: z
      .object({
        wpId: z.number().int().optional(),
        url: z.url().optional(),
        modified: z.coerce.date().optional(),
        thumbnail: z.url().optional(),
        gallery: z.array(z.url()).default([]),
      })
      .optional(),
  }),
});

export const collections = { pages, offers, blog, work };
