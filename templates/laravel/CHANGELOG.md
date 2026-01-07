# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Nothing yet

## [0.1.0] - {{CURRENT_DATE}}

### Added
- Initial project setup with Laravel 12
- Laravel Breeze with Livewire stack for authentication
- Livewire 3 and Volt for reactive components
- Tailwind CSS v4 with dark mode support
- @tailwindcss/forms plugin for beautiful form elements
- Vite 7 for asset bundling with hot module replacement (HMR)
- SEO components (meta tags, Open Graph, JSON-LD)
- Custom error pages (404, 500)
- SQLite database (default configuration)
- PHPUnit testing framework
- Comprehensive README documentation

### Infrastructure
- Pre-commit hooks for automated quality checks (Pint, PHPStan)
- GitHub Actions CI/CD workflow
- Dependabot for automated dependency updates (Composer, npm, GitHub Actions)
- PHPStan (Larastan) level 7 for static analysis with baseline
- Laravel Pint for code formatting (PSR-12)
- Queue system with database driver
- Real-time logging with Laravel Pail

### Developer Experience
- Composer scripts for common tasks (`setup`, `dev`, `test`, `analyse`, `check`)
- Development environment setup with `composer dev` (concurrent services)
- Automated quality checks on commit
- Code style enforcement
- Static analysis baseline

---

[Unreleased]: https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/releases/tag/v0.1.0
