import { z } from 'astro/zod';
import raw from '../data/ui.json';

const link = z.object({ label: z.string(), href: z.string() });

// Interface text (labels, buttons, messages). Edited in Sveltia CMS under "Site settings → Interface text".
const uiSchema = z.object({
  header: z.object({ skipToContent: z.string(), openMenu: z.string(), menuTitle: z.string() }),
  footer: z.object({
    tagline: z.string(),
    links: z.array(link),
    rssLabel: z.string(),
  }),
  pricing: z.object({ featuredDefaultLabel: z.string() }),
  work: z.object({
    title: z.string(),
    description: z.string(),
    lede: z.string(),
    empty: z.string(),
    eyebrow: z.string(),
    typeLabel: z.string(),
    clientLabel: z.string(),
    dateLabel: z.string(),
    visitSiteLabel: z.string(),
    screenshotAltSuffix: z.string(),
    moreWorkLabel: z.string(),
    types: z.object({ brand: z.string(), web: z.string(), print: z.string(), app: z.string() }),
  }),
  blog: z.object({
    title: z.string(),
    description: z.string(),
    lede: z.string(),
    empty: z.string(),
    eyebrow: z.string(),
    homeHeading: z.string(),
    updatedLabel: z.string(),
    draftLabel: z.string(),
    feedTitle: z.string(),
  }),
  contact: z.object({
    nameLabel: z.string(),
    emailLabel: z.string(),
    companyLabel: z.string(),
    offerLabel: z.string(),
    offerOther: z.string(),
    messageLabel: z.string(),
    messagePlaceholder: z.string(),
    submitLabel: z.string(),
    sendingLabel: z.string(),
    successTitle: z.string(),
    successText: z.string(),
    errorTitle: z.string(),
    errorText: z.string(),
    notConfiguredTitle: z.string(),
    notConfiguredText: z.string(),
    tokenPending: z.string(),
    errors: z.record(z.string(), z.string()),
  }),
  notFound: z.object({ title: z.string(), lede: z.string(), backLabel: z.string() }),
});

export const ui = uiSchema.parse(raw);
