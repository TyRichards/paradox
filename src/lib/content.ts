import { getCollection, getEntry, type CollectionEntry } from 'astro:content';
import { WORK_TYPES } from '../content.config';
import { site } from './site';

export type WorkType = (typeof WORK_TYPES)[number];

export { WORK_TYPES };

export async function getPage(id: string) {
  const page = await getEntry('pages', id);
  if (!page) throw new Error(`Missing page: src/content/pages/${id}.md`);
  return page;
}

/** Published work only. Unpublished entries stay in the repo but get no page and no listing. */
export async function getWork(): Promise<CollectionEntry<'work'>[]> {
  const entries = await getCollection('work', ({ data }) => data.published);
  return entries.sort((a, b) => a.data.order - b.data.order || a.data.title.localeCompare(b.data.title));
}

// Skips the collection lookup (and Astro's "collection is empty" warning) while the blog has no files.
const blogFileCount = Object.keys(import.meta.glob('/src/content/blog/**/*.md')).length;

export async function getPosts(): Promise<CollectionEntry<'blog'>[]> {
  if (blogFileCount === 0) return [];
  const posts = await getCollection('blog', ({ data }) => import.meta.env.DEV || !data.draft);
  return posts.sort((a, b) => b.data.pubDate.valueOf() - a.data.pubDate.valueOf());
}

/** Site nav from site.json, minus the Blog link while there are no posts to show. */
export async function getNav() {
  const showBlog = await hasPosts();
  return site.nav.filter((item) => showBlog || item.href.replace(/\/$/, '') !== '/blog');
}

export async function hasPosts() {
  return (await getPosts()).length > 0;
}

export async function getOffers(): Promise<CollectionEntry<'offers'>[]> {
  const offers = await getCollection('offers');
  return offers.sort((a, b) => a.data.order - b.data.order);
}

export function offerContactHref(offerId: string) {
  return `/contact/?offer=${encodeURIComponent(offerId)}`;
}

export function formatDate(date: Date) {
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' });
}
