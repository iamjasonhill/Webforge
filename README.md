# WebForge

> Build websites with purpose. Best practices baked in from the start.

WebForge is a CLI tool for scaffolding web projects with SEO, performance, and
quality practices built-in from day one. Built with
[Laravel Zero](https://laravel-zero.com/).

## Features

- 🚀 **Multi-platform support** - Laravel, WordPress, Astro
- 🔍 **SEO-first** - Meta tags, sitemaps, structured data scaffolded
  automatically
- 🔧 **CLI-driven** - Repeatable, version-controlled project setup
- 📊 **Audit tool** - Check projects for best practices compliance
- 🧠 **Brain integration** - Send events to Brain Nucleus for monitoring

## Installation

```bash
# Clone and install
git clone https://github.com/iamjasonhill/Webforge.git
cd Webforge
composer install

# Make globally available (optional)
composer global require webforge/cli
```

## Usage

### Initialize a New Project

```bash
# Interactive wizard
./webforge init

# With options
./webforge init --platform=laravel --name=my-site --with-brain --with-seo

# See all options
./webforge init --help
```

### Audit an Existing Project

```bash
# Audit current directory
./webforge audit

# Audit specific path
./webforge audit /path/to/project

# SEO audit only
./webforge audit --seo
```

## Supported Platforms

| Platform  | Status     | Description                      |
| --------- | ---------- | -------------------------------- |
| Laravel   | ✅ Ready   | Laravel 12 + Livewire + Tailwind |
| Astro     | ✅ Ready   | TypeScript + Tailwind + Vercel   |
| WordPress | 📋 Planned | WP-CLI managed setup             |

## What Gets Scaffolded

### Laravel Projects

When you run `./webforge init --platform=laravel`, you get:

- ✅ Laravel 12 project via Composer
- ✅ Laravel Breeze with Livewire stack
- ✅ PHPStan for static analysis
- ✅ Pint for code style (`pint.json`)
- ✅ PHPStan config (`phpstan.neon`)
- ✅ SEO components (`<x-seo-head>`, `<x-json-ld>`)
- ✅ SEO config (`config/seo.php`)
- ✅ Brain Nucleus client (optional)
- ✅ Composer scripts (`dev`, `analyse`, `check`)
- ✅ NPM dependencies installed

## Template Files

```
templates/laravel/
├── components/
│   ├── seo-head.blade.php    # Meta tags, OG, Twitter cards
│   ├── json-ld.blade.php     # Structured data (6 schema types)
│   ├── image.blade.php       # Optimized image with lazy loading
│   ├── breadcrumbs.blade.php # Breadcrumb nav with Schema.org
│   └── analytics.blade.php   # GA4/GTM/Plausible support
├── config/
│   ├── pint.json             # Laravel Pint code style
│   ├── phpstan.neon          # PHPStan static analysis
│   └── seo.php               # SEO configuration
├── errors/
│   ├── 404.blade.php         # Custom 404 page
│   └── 500.blade.php         # Custom 500 page
├── middleware/
│   └── SecurityHeaders.php   # Security headers middleware
├── public/
│   ├── robots.txt            # Search engine crawling rules
│   └── manifest.json         # PWA web manifest
├── views/
│   └── sitemap.blade.php     # Dynamic XML sitemap
├── routes/
│   └── sitemap-route.php     # Sitemap route definition
├── scripts/
│   └── pre-commit            # Git pre-commit hook
└── workflows/
    └── ci.yml                # GitHub Actions CI workflow
```

## Roadmap

- [x] Laravel scaffolding with Livewire + Tailwind
- [x] SEO components (meta tags, Open Graph, JSON-LD)
- [x] PHPStan + Pint configuration
- [x] Brain Nucleus integration
- [x] Project audit command (10 checks)
- [x] Pre-commit hook template
- [x] CI/CD workflow template
- [x] Optimized image component
- [x] robots.txt + sitemap templates
- [x] Security headers middleware
- [x] Custom error pages (404/500)
- [x] Breadcrumb component
- [x] PWA web manifest
- [x] Analytics component
- [ ] WordPress WP-CLI scaffolding
- [ ] Astro scaffolding
- [ ] Template versioning
- [ ] Publish to Packagist

## Development

```bash
# Run tests
./webforge test

# Build single-file executable
./webforge app:build
```

## License

MIT
