# WebForge

> Build websites with purpose. Best practices baked in from the start.

WebForge is a CLI tool for scaffolding web projects with SEO, performance, and quality practices built-in from day one.

## Features

- 🚀 **Multi-platform support** - Laravel, WordPress, Astro
- 🔍 **SEO-first** - Meta tags, sitemaps, structured data scaffolded automatically
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

| Platform | Status | Description |
|----------|--------|-------------|
| Laravel | 🚧 WIP | Laravel 12 + Livewire + Tailwind |
| WordPress | 📋 Planned | WP-CLI managed setup |
| Astro | 📋 Planned | Static/SSR site generator |

## What Gets Scaffolded

### Laravel Projects
- ✅ pint.json (code style)
- ✅ phpstan.neon (static analysis)
- ✅ Pre-commit hooks
- ✅ CI/CD workflow
- ✅ SEO components (meta tags, Open Graph, JSON-LD)
- ✅ Sitemap & robots.txt
- ✅ Brain Nucleus integration

## Roadmap

- [ ] Complete Laravel scaffolding
- [ ] WordPress WP-CLI scaffolding
- [ ] Astro scaffolding
- [ ] Template versioning
- [ ] Plugin system for custom templates

## License

MIT
