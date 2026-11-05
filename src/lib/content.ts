import { getCollection, getEntry, type CollectionEntry } from 'astro:content';
import { WORK_TYPES } from '../content.config';

export type WorkType = (typeof WORK_TYPES)[number];

export const WORK_TYPE_LABELS: Record<WorkType, string> = {
  brand: 'Brand',
  web: 'Web',
  print: 'Print',
  app: 'App',
};

export { WORK_TYPES };

export async function getPage(id: string) {
  const page = await getEntry('pages', id);
  if (!page) throw new Error(`Missing page: src/content/pages/${id}.md`);
  return page;
}

export async function getWork(): Promise<CollectionEntry<'work'>[]> {
  const entries = await getCollection('work');
  return entries.sort((a, b) => a.data.order - b.data.order || a.data.title.localeCompare(b.data.title));
}

export async function getPosts(): Promise<CollectionEntry<'blog'>[]> {
  const posts = await getCollection('blog', ({ data }) => import.meta.env.DEV || !data.draft);
  return posts.sort((a, b) => b.data.pubDate.valueOf() - a.data.pubDate.valueOf());
}

export async function getServices(): Promise<CollectionEntry<'services'>[]> {
  const entries = await getCollection('services');
  return entries.sort((a, b) => a.data.order - b.data.order);
}

export function formatDate(date: Date) {
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' });
}
