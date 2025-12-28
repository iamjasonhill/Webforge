# Website Project Setup Checklist

This checklist helps ensure consistent setup across all website projects.

> **Tip:** Run `webforge docs:update` to fetch the latest version of this
> checklist.

---

## Quick Start

1. [ ] Answer the [Pre-Project Questionnaire](#pre-project-questionnaire)
2. [ ] Set up [Favicons & PWA](#favicons--pwa)
3. [ ] Configure [Environment Variables](#environment-configuration)
4. [ ] Set up [Brain Analytics](#brain-analytics)
5. [ ] Complete [Pre-Launch Checks](#pre-launch-checks)

---

## Pre-Project Questionnaire

- [ ] Business name and tagline?
- [ ] Brand colors (hex codes)?
- [ ] Logo available? (SVG preferred)
- [ ] Production domain?
- [ ] www or non-www canonical?
- [ ] Hosting platform? (Vercel/Netlify)
- [ ] Brain Analytics needed?
- [ ] Heartbeat monitoring? (**Disable for static sites**)

---

## Favicons & PWA

> **Do this early!** Don't leave the default Astro favicon.

- [ ] `/public/favicon.svg`
- [ ] `/public/favicon-16x16.png`
- [ ] `/public/favicon-32x32.png`
- [ ] `/public/apple-touch-icon.png`
- [ ] Update `manifest.json` with project name

---

## Environment Configuration

```env
PUBLIC_SITE_URL=https://yourdomain.com.au
PUBLIC_SITE_NAME="Your Site Name"
PUBLIC_SITE_DESCRIPTION="Description"
PUBLIC_BRAIN_URL=https://again.com.au
PUBLIC_BRAIN_KEY=brn_xxxx
```

---

## Brain Analytics

1. [ ] Create project in Brain Admin
2. [ ] Create API key
3. [ ] **Disable heartbeat for static sites**
4. [ ] Copy `brain-analytics.js` to `/public/`
5. [ ] Add `BrainAnalytics.astro` component

---

## Pre-Launch Checks

- [ ] `npm run build` succeeds
- [ ] `npm run lint` passes
- [ ] Favicon visible
- [ ] Sitemap generated
- [ ] Mobile responsive
- [ ] All links working

---

## Common Gotchas

| Issue                   | Solution             |
| ----------------------- | -------------------- |
| ENV vars not working    | Use `PUBLIC_` prefix |
| health.missed incidents | Disable heartbeat!   |

---

## Update This Checklist

```bash
webforge docs:update
```

_Full guide:
[brain-client/web/PROJECT-CHECKLIST.md](https://github.com/iamjasonhill/thebrain/blob/main/brain-client/web/PROJECT-CHECKLIST.md)_
