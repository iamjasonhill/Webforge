# Changelog

All notable changes to WebForge will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Changed

- Brain Nucleus client is now **always installed** on Laravel projects (no
  longer opt-in)
- Removed `--with-brain` option from init command

### Added

- WordPress scaffolding (coming soon)
- Astro npm package for Brain Nucleus (coming soon)
- Standalone PHP client for static sites (coming soon)

---

## [0.3.0] - 2023-12-23

### Added

- Full Astro scaffolding support
- Astro SEO component (meta tags, OG, Twitter)
- Astro Schema component (7 JSON-LD types)
- Astro Breadcrumbs component
- Astro Analytics component (GA4, GTM, Plausible)
- Custom 404 page for Astro
- Deployment configs (vercel.json, netlify.toml)
- ESLint + Prettier config for Astro
- GitHub Actions CI for Astro

---

## [0.2.0] - 2023-12-23

### Added

- Security headers middleware template
- Custom 404/500 error page templates
- Breadcrumbs component with Schema.org markup
- Extended JSON-LD (Article, FAQ, Product, LocalBusiness types)
- PWA web manifest template
- Analytics component (GA4, GTM, Plausible)
- Audit checks for security headers, error pages, web manifest
- Proper test suite (13 tests)
- FUTURE.md for tracking enhancements

### Changed

- Audit now has 10 total checks

---

## [0.1.0] - 2023-12-23

### Added

- Initial Laravel scaffolding with Livewire + Tailwind
- SEO components (seo-head, json-ld, image)
- robots.txt template
- Sitemap route and view
- PHPStan + Pint configuration
- Pre-commit hook template
- GitHub Actions CI workflow
- Brain Nucleus integration option
- Project audit command (7 checks)
