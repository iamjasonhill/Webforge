# Contributing to WebForge

Thank you for considering contributing to WebForge!

## Development Setup

```bash
git clone https://github.com/iamjasonhill/Webforge.git
cd Webforge
composer install
```

## Running Tests

```bash
./webforge test
```

## Code Style

This project uses Laravel Pint for code style. Run before committing:

```bash
./vendor/bin/pint
```

## Pull Request Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`./webforge test`)
5. Run Pint (`./vendor/bin/pint`)
6. Commit your changes
7. Push to your fork
8. Open a Pull Request

## Adding New Templates

Templates are stored in `templates/{platform}/`. When adding new templates:

1. Create the template file
2. Update `InitCommand.php` to copy the template
3. Update `AuditCommand.php` if relevant checks should be added
4. Add tests for the new functionality
5. Update the README.md template tree
6. Update CHANGELOG.md

## Reporting Issues

Please use GitHub Issues to report bugs or request features.
