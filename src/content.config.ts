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
    heading: z.string().optional(),
    ctas: z.array(link).optional(),
    placeholder: z.boolean().default(false),
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
    placeholder: z.boolean().default(false),
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
    types: z.array(z.enum(WORK_TYPES)).default([]),
    client: z.string().optional(),
    projectDate: z.string().optional(),
    order: z.number().int().default(999),
    cover: z.string().optional(),
    coverAlt: z.string().optional(),
    gallery: z.array(z.string()).default([]),
    featured: z.boolean().default(false),
    placeholder: z.boolean().default(false),
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

const services = defineCollection({
  loader: glob({ pattern: '*.md', base: './src/content/services' }),
  schema: z.object({
    title: z.string(),
    summary: z.string(),
    workType: z.enum(WORK_TYPES).optional(),
    order: z.number().int().default(999),
    placeholder: z.boolean().default(false),
  }),
});

export const collections = { pages, blog, work, services };
