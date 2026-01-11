# {{PROJECT_NAME}}

{{PROJECT_DESCRIPTION}}

<p align="center">
  <a href="https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/actions/workflows/ci.yml"><img src="https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/workflows/CI/badge.svg" alt="CI Status"></a>
  <a href="https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/php-%5E8.2-777BB4?logo=php&logoColor=white" alt="PHP Version"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel Version"></a>
</p>

---

## Features

- 🔐 Authentication with Laravel Breeze
- 🎨 Tailwind CSS v4 with dark mode support
- 🤖 Laravel Boost for AI-assisted development
- ⚡ Vite for fast asset bundling
- 🚦 Laravel Horizon for queue monitoring
- 🔌 Livewire Volt for functional components
- 🛡️ Laravel Sanctum for API authentication
- 🧪 PHPUnit testing setup
- 🔍 Code quality tools (Pint, PHPStan)
- 🚀 GitHub Actions CI/CD
- 🤖 Dependabot for automated updates
- 📝 Pre-commit hooks for quality gates

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Livewire, Tailwind CSS v4
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Queue**: Database driver (easily switchable to Redis)
- **Build**: Vite with Hot Module Replacement
- **Testing**: PHPUnit
- **Code Quality**: Laravel Pint, PHPStan (Larastan)

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 20+ and npm
- SQLite (or another database)

## Installation

### Quick Start

```bash
# Clone the repository
git clone https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}.git
cd {{REPO_NAME}}

# Run automated setup
composer setup
```

The `composer setup` command will:
- Install PHP dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run database migrations
- Install Node.js dependencies
- Build frontend assets

### Manual Setup

If you prefer manual installation:

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build
```

## Development

### Running the Development Server

Use the convenient `dev` script to run all services concurrently:

```bash
composer dev
```

This starts:
- Laravel development server (http://localhost:8000)
- Queue worker for background jobs
- Pail logs for real-time log viewing
- Vite dev server with hot module replacement

### Individual Services

Or run services separately:

```bash
php artisan serve              # Development server
php artisan queue:listen       # Queue worker
php artisan pail               # Log viewer
npm run dev                    # Vite dev server
```

## Testing

### Run All Tests

```bash
composer test
```

### Code Quality Checks

```bash
# Format code
./vendor/bin/pint

# Check code style without formatting
./vendor/bin/pint --test

# Run static analysis
composer analyse

# Run all quality checks (format, analyze, test)
composer check
```

### Pre-commit Hooks

The repository includes pre-commit hooks that automatically run:
- Laravel Pint (code formatting)
- PHPStan (static analysis)

These run automatically before each commit to ensure code quality.

## Deployment

### Build for Production

```bash
npm run build
php artisan optimize
```

### Environment Variables

Make sure to set these in production:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` - Your production URL
- Database credentials
- Queue driver (consider Redis for production)

## Project Structure

```
{{REPO_NAME}}/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Livewire/
│   └── ...
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── public/
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please review our [Security Policy](SECURITY.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a history of changes to this project.

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- 🐛 [Report a bug](https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/issues)
- 💡 [Request a feature](https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/issues)
- 💬 [Ask a question](https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/discussions)

---

<p align="center">
  Built with ❤️ using Laravel
</p>
