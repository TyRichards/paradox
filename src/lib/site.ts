import { z } from 'astro/zod';
import raw from '../data/site.json';

const link = z.object({ label: z.string(), href: z.string() });

// src/data/site.json is edited by Sveltia CMS ("Site settings"), so validate it at build time.
const siteSchema = z.object({
  name: z.string(),
  title: z.string(),
  description: z.string(),
  owner: z.string(),
  emailDisplay: z.string(),
  nav: z.array(link),
  navCta: link.optional(),
});

export const site = siteSchema.parse(raw);
