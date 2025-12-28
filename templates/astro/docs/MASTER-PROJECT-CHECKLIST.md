# Master Project Checklist - Astro Website

> **Source of Truth** for all website projects. Copy to your project using
> `webforge init` or manually. Update local copy using `webforge docs:update`.

---

## Phase 0: Pre-Project Discovery

> Complete BEFORE starting any code work.

### Business & Brand

- [ ] Business name confirmed
- [ ] Tagline/value proposition defined
- [ ] Primary brand colors (Primary, Secondary, Accent)
- [ ] Logo asset available (SVG preferred)
- [ ] Target audience documented

### Domain & Hosting

- [ ] Production domain confirmed
- [ ] www vs non-www decision (for canonical)
- [ ] Hosting platform decided (Vercel / Netlify / Cloudflare Pages)
- [ ] DNS access confirmed
- [ ] Existing redirects documented (if migrating)

### GSC Baseline & Content Audit

> ⚠️ **CRITICAL for site revamps/refreshes**

- [ ] Google Search Console access confirmed
- [ ] Run `research-engine google performance "[domain]" --days 90`
- [ ] Identify top performing pages (DO NOT TOUCH these)
- [ ] Identify pages with potential (good position, low clicks → optimize)
- [ ] Identify pages to delete/merge (cannibalization, zero value)
- [ ] Document 301 redirect mapping for deleted pages
- [ ] Push baseline snapshot to Brain:
  ```bash
  research-engine baseline "[Site Name]" "[URL]" --name "pre-launch" --days 90
  ```

### Content Strategy

- [ ] Identify 3-5 content pillars based on GSC data + competitor analysis
- [ ] Run competitor analysis:
      `research-engine competitor analyze "[competitor URL]"`
- [ ] Generate content blueprints: `research-engine blueprint "[domain]"`
- [ ] Document target keywords for each pillar page

---

## Phase 1: Repository & Structure

- [ ] GitHub repo created
- [ ] Git initialized with initial commit
- [ ] `package.json` configured with project name
- [ ] `astro.config.mjs` with production site URL
- [ ] `tailwind.config.mjs` with brand color tokens
- [ ] `.gitignore`, `.prettierrc`, `eslint.config.js`
- [ ] `README.md` with design tokens documented

---

## Phase 2: Favicons & PWA

> ⚠️ Do this EARLY - don't ship with default Astro rocket!

### Generate Favicons (when logo ready)

```bash
cd public
magick -background none logo.svg -resize 32x32 favicon.ico
magick -background none logo.svg -resize 180x180 apple-touch-icon.png
magick -background none logo.svg -resize 192x192 android-chrome-192x192.png
magick -background none logo.svg -resize 512x512 android-chrome-512x512.png
cp logo.svg favicon.svg
```

### Required Files

- [ ] `/public/favicon.svg`
- [ ] `/public/favicon.ico` (32x32)
- [ ] `/public/apple-touch-icon.png` (180x180)
- [ ] `/public/android-chrome-192x192.png`
- [ ] `/public/android-chrome-512x512.png`
- [ ] `/public/logo.svg`
- [ ] `/public/og-image.jpg` (1200x630 for social sharing)

### manifest.json

- [ ] `name` set to project name
- [ ] `short_name` set
- [ ] `description` added
- [ ] `theme_color` matches brand primary
- [ ] `background_color` set
- [ ] Icon paths updated

---

## Phase 3: Core Components

- [ ] `SEO.astro` - Meta tags, OG, Twitter cards
- [ ] `Layout.astro` - Main layout wrapper
- [ ] `Header.astro` - Navigation
- [ ] `Footer.astro` - Footer with legal links
- [ ] `Schema.astro` - JSON-LD structured data
- [ ] `Breadcrumbs.astro` - Breadcrumb navigation

### Schema Types Required

- [ ] `WebSite` (homepage)
- [ ] `Organization` or `LocalBusiness` (homepage)
- [ ] `Article` or `Service` (content pages)
- [ ] `FAQPage` (any page with FAQ section)
- [ ] `BreadcrumbList` (all internal pages)

---

## Phase 4: Analytics Integration

### Brain Analytics (Primary)

- [ ] Create project in Brain Admin (`/admin/services`)
- [ ] Create API key
- [ ] **Toggle OFF Heartbeat Monitoring** (static sites only)
- [ ] Copy `brain-analytics.js` to `/public/`
- [ ] Create `BrainAnalytics.astro` component
- [ ] Add to `Layout.astro`

### GA4 (Optional Fallback)

- [ ] Add `PUBLIC_GA_ID` to `.env`
- [ ] Configure `Analytics.astro` component

---

## Phase 5: Environment Configuration

### .env.example Template

```env
# Site Configuration
PUBLIC_SITE_URL=https://example.com.au
PUBLIC_SITE_NAME="Project Name"
PUBLIC_SITE_DESCRIPTION="Site description"
PUBLIC_SITE_IMAGE=/og-image.jpg

# Brain Analytics
PUBLIC_BRAIN_URL=https://again.com.au
PUBLIC_BRAIN_KEY=brn_xxxx

# Optional
PUBLIC_GA_ID=G-XXXXXXXXXX
```

### Hosting Platform

- [ ] Add all `PUBLIC_*` vars to Vercel/Netlify
- [ ] Note: Vars bake in at build time (redeploy after changes)

---

## Phase 6: LLM Search Readiness

> Make your site AI-friendly for ChatGPT, Perplexity, Claude, etc.

### llms.txt File

- [ ] Create `/public/llms.txt` with:
  - Brand name and ownership
  - Primary services/topics
  - Geographic coverage
  - Preferred pages for citations
  - Key facts (numbers, stats, differentiators)
  - Content guidelines for AI
  - Contact information

### Content Optimization for AI

- [ ] Clear, factual content (avoid fluff)
- [ ] FAQ sections with schema markup
- [ ] Comparison tables with structured data
- [ ] Definitive answers to common questions
- [ ] Citation-worthy statistics

---

## Phase 7: Content Pages

### Required Pages

- [ ] `/` - Homepage
- [ ] `/404` - Custom 404 page
- [ ] `/robots.txt` - Dynamic robots.txt
- [ ] Pillar pages (from Phase 0 content strategy)

### Standard Pages

- [ ] `/about` - About page
- [ ] `/contact` - Contact page with form
- [ ] `/privacy` - Privacy Policy
- [ ] `/terms` - Terms & Conditions

### Content Requirements

- [ ] Every page has unique `<title>` and `<meta description>`
- [ ] Every page has appropriate Schema type
- [ ] Pillar pages have internal links to related content
- [ ] FAQ sections use `<details>` with FAQPage schema

---

## Phase 8: Deployment Configuration

- [ ] `netlify.toml` configured (if using Netlify)
- [ ] `vercel.json` configured (if using Vercel)
- [ ] GitHub Actions CI workflow (`.github/workflows/ci.yml`)
- [ ] 301 redirects implemented (from Phase 0 audit)

---

## Phase 9: Pre-Launch Verification

### Technical Checks

- [ ] `npm run build` succeeds
- [ ] `npm run lint` passes
- [ ] `npm run format:check` passes
- [ ] All pages have unique titles/descriptions
- [ ] Canonical URLs point to production domain
- [ ] Favicon visible in browser tab
- [ ] manifest.json accessible
- [ ] sitemap-index.xml generated
- [ ] robots.txt accessible

### Visual Checks

- [ ] Open Graph preview works (use ogimage.dev or similar)
- [ ] Mobile responsive verified (real device)
- [ ] Lighthouse performance score 90+
- [ ] Lighthouse accessibility score 90+

### Analytics Checks

- [ ] Brain project configured correctly
- [ ] Heartbeat disabled (static site)
- [ ] Test pageview appears in Brain dashboard
- [ ] GA4 tracking working (if used)

### Content Checks

- [ ] All placeholder text replaced
- [ ] All links working (no 404s)
- [ ] Phone numbers clickable (`tel:`)
- [ ] Email addresses clickable (`mailto:`)
- [ ] Forms submitting correctly

### Legal Checks

- [ ] Cookie consent banner (if required)
- [ ] Privacy policy accessible
- [ ] Terms accessible
- [ ] ABN/business details correct

---

## Phase 10: Post-Launch Analytics

> Run within 24 hours of going live.

### GSC Verification

- [ ] Verify site ownership in Google Search Console
- [ ] Submit sitemap: `sitemap-index.xml`
- [ ] Request indexing for key pages

### Baseline Capture

- [ ] Capture post-launch baseline:
  ```bash
  research-engine baseline "[Site Name]" "[URL]" --name "post-launch" --days 28
  ```
- [ ] Schedule weekly/monthly snapshots

### Monitoring Setup

- [ ] Brain dashboard configured
- [ ] Set up alerts for errors (if applicable)
- [ ] Compare Week 1 vs Week 4 performance

---

## Common Gotchas ⚠️

| Issue                            | Solution                                    |
| -------------------------------- | ------------------------------------------- |
| ENV vars not working in Vercel   | Must use `PUBLIC_` prefix                   |
| Dev server ignores ENV changes   | Restart `npm run dev`                       |
| Prettier fails on inline scripts | Use `set:html` pattern                      |
| www vs non-www inconsistent      | Set canonical in SEO.astro                  |
| Favicon not showing              | Check it's in `/public/`                    |
| health.missed incidents          | Disable heartbeat for static sites          |
| GSC shows 0 data                 | Check you're querying `sc-domain:` property |

---

## Quick Reference Commands

```bash
# Development
npm run dev              # Start dev server (localhost:4321)
npm run build            # Production build
npm run preview          # Preview build locally
npm run lint             # ESLint check
npm run format           # Prettier format

# Research Engine
research-engine google sites                    # List verified GSC sites
research-engine google performance "[site]"    # Get GSC performance data
research-engine baseline "[name]" "[url]"      # Capture SEO baseline
research-engine report "[site]"                # Generate site report
research-engine blueprint "[domain]"           # Generate content strategy
```

---

_Last Updated: December 2025_ _Based on: Moving Again, Moving Insurance, and
Brain Project learnings_
