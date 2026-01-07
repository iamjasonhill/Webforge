# Contributing to {{PROJECT_NAME}}

Thank you for your interest in contributing! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Development Workflow](#development-workflow)
- [Code Style](#code-style)
- [Testing](#testing)
- [Commit Messages](#commit-messages)
- [Pull Request Process](#pull-request-process)

## Getting Started

Before contributing, please:

1. Read the [README.md](README.md) to understand the project
2. Check existing [issues](https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/issues)
3. Review the [CHANGELOG.md](CHANGELOG.md) to see recent changes

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 20+ and npm
- SQLite (or another Laravel-supported database)

### Initial Setup

1. **Fork and clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/{{REPO_NAME}}.git
   cd {{REPO_NAME}}
   ```

2. **Run the setup script**
   ```bash
   composer setup
   ```
   
   This automatically:
   - Installs PHP dependencies
   - Copies `.env.example` to `.env`
   - Generates application key
   - Runs database migrations
   - Installs Node.js dependencies
   - Builds frontend assets

3. **Configure your environment**
   
   Edit `.env` file as needed for your local development.

### Running the Development Server

Use the `dev` script to run all services concurrently:

```bash
composer dev
```

This starts:
- Laravel development server (http://localhost:8000)
- Queue worker
- Pail logs
- Vite dev server

## Development Workflow

1. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Write clean, readable code
   - Follow Laravel conventions
   - Add tests for new functionality
   - Update documentation as needed

3. **Run quality checks**
   ```bash
   composer check
   ```
   
   This runs:
   - Laravel Pint (code formatting)
   - PHPStan (static analysis)
   - PHPUnit (tests)

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: add new feature"
   ```

5. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Open a Pull Request**

## Code Style

We use **Laravel Pint** for automatic code formatting. The configuration follows PSR-12 standards.

### Pre-commit Hooks

The repository has a pre-commit hook (`.git/hooks/pre-commit`) that automatically runs when you commit:

1. **Laravel Pint** - Automatically formats staged PHP files
2. **PHPStan** - Runs static analysis to catch potential issues
3. **Auto-staging** - Re-adds formatted files to your commit

The hook will:
- ✅ Skip checks if no PHP files are staged
- ✅ Auto-format code with Pint
- ❌ Block commits if PHPStan finds issues

If the pre-commit hook blocks your commit, fix the issues and try again.

### Running Code Formatter

```bash
# Format code
./vendor/bin/pint

# Check without modifying
./vendor/bin/pint --test
```

### Style Guidelines

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use type hints for parameters and return types
- Write descriptive variable and method names
- Add PHPDoc blocks for complex methods
- Keep methods focused and single-purpose

### Example

```php
<?php

namespace App\Services;

class ExampleService
{
    /**
     * Process the given data.
     */
    public function process(array $data): array
    {
        $filtered = $this->filterData($data);
        
        return $this->transformData($filtered);
    }
    
    private function filterData(array $data): array
    {
        // Implementation
    }
    
    private function transformData(array $data): array
    {
        // Implementation
    }
}
```

## Testing

We use **PHPUnit** for testing. All tests are in the `tests/` directory.

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage
php artisan test --coverage
```

### Writing Tests

- Write tests for all new features
- Ensure existing tests still pass
- Aim for high code coverage
- Use feature tests for end-to-end flows
- Use unit tests for isolated logic

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_example_feature_works(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
    }
}
```

## Commit Messages

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Format

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types

- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes (formatting)
- `refactor:` Code refactoring
- `perf:` Performance improvements
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

### Examples

```bash
feat(auth): add two-factor authentication
fix(api): handle null response from external service
docs(readme): update installation instructions
refactor(controllers): extract common validation logic
test(users): add tests for user deletion
chore(deps): update Laravel to 12.1
```

## Pull Request Process

1. **Ensure your PR**:
   - Has a clear, descriptive title
   - References related issues (e.g., "Closes #123")
   - Includes a description of changes
   - Has passing tests (`composer check`)
   - Updates documentation if needed
   - Updates CHANGELOG.md under `[Unreleased]`

2. **PR Template**:
   ```markdown
   ## Description
   Brief description of what this PR does
   
   ## Type of Change
   - [ ] Bug fix
   - [ ] New feature
   - [ ] Breaking change
   - [ ] Documentation update
   
   ## Testing
   How has this been tested?
   
   ## Checklist
   - [ ] Code follows style guidelines
   - [ ] Self-reviewed my code
   - [ ] Added tests
   - [ ] Updated documentation
   - [ ] Updated CHANGELOG.md
   ```

3. **Wait for review**
   - Address feedback promptly
   - Keep discussions focused
   - Update your branch if needed

## Questions?

If you have questions or need help:

- Open an [issue](https://github.com/{{GITHUB_USERNAME}}/{{REPO_NAME}}/issues)
- Review existing documentation

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
