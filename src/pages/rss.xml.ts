import rss from '@astrojs/rss';
import type { APIContext } from 'astro';
import { getPosts } from '../lib/content';
import { ui } from '../lib/ui';

export async function GET(context: APIContext) {
  const posts = await getPosts();
  return rss({
    title: ui.blog.feedTitle,
    description: ui.blog.description,
    site: context.site!,
    items: posts.map((post) => ({
      title: post.data.title,
      description: post.data.description,
      pubDate: post.data.pubDate,
      link: `/blog/${post.id}/`,
    })),
  });
}
