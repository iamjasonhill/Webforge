# WordPress Migration Strategy

> Strategy for migrating WordPress sites to Astro/Static PHP platforms.

---

## Site Tiers

### Tier 1: Quick Wins (Static PHP)

- **Type**: Small brochure sites
- **Target**: Static PHP
- **Approach**: Extract content, build with WebForge static-php

### Tier 2: Programmatic Sites (Astro)

- **Type**: Large page count, reproducible content
- **Target**: Astro
- **Approach**: Build data pipeline, generate pages programmatically

### Tier 3: Money Sites (Astro + Careful Planning)

- **Type**: Revenue/SEO critical
- **Target**: Astro
- **Approach**: Staged migration with redirects, SEO preservation

---

## Migration Process

### 1. Inventory Sites

- [ ] List all WP sites
- [ ] Assign tier (1-3)
- [ ] Set priority order

### 2. Per-Site Migration

#### Content Extraction

- Export posts/pages to Markdown
- Export menus to JSON
- Download media assets
- Document custom functionality

#### SEO Preservation

- Map all existing URLs
- Generate redirect rules
- Preserve meta tags
- Maintain canonical URLs
- Submit new sitemap

#### Staging & Testing

- Build new site on staging
- Test all redirects
- Verify SEO elements
- Check mobile/accessibility

#### Go-Live

- Update DNS
- Monitor for 404s
- Check search console
- Verify analytics

---

## Future WebForge Enhancements

When ready to implement:

- [ ] WP content extraction script
- [ ] Markdown converter for WP posts
- [ ] Redirect generator (WP URLs → new)
- [ ] Migration checklist command

---

## Notes

- WordPress scaffolding in WebForge is **not needed** since goal is migration
  away
- Focus on tools that help extraction/migration
- Ploi.io can host both WP (during transition) and static sites
