# Security Policy

## Reporting a Vulnerability

We take the security of this project seriously. If you discover a security vulnerability, please report it responsibly.

### How to Report

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to:

- **Email**: {{AUTHOR_EMAIL}}
- **Subject**: [{{PROJECT_NAME}}] Security Vulnerability Report

### What to Include

To help us triage and fix the issue quickly, please include:

1. **Description** of the vulnerability
2. **Steps to reproduce** the issue
3. **Potential impact** of the vulnerability
4. **Suggested fix** (if you have one)
5. **Your contact information** for follow-up questions

### Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Timeline**: Depends on severity, but we aim for:
  - Critical: 1-7 days
  - High: 7-14 days
  - Medium: 14-30 days
  - Low: 30-90 days

### Disclosure Policy

- Please give us reasonable time to fix the vulnerability before public disclosure
- We will acknowledge your contribution in the security advisory (unless you prefer to remain anonymous)
- We will keep you informed about our progress

## Supported Versions

We release patches for security vulnerabilities for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Security Best Practices

When using this application:

1. **Keep dependencies updated**: Run `composer update` and `npm update` regularly
2. **Use environment variables**: Never commit `.env` files with sensitive data
3. **Secure your instance**: Always use HTTPS in production
4. **Database security**: Use strong credentials and restrict access
5. **Queue workers**: Run queue workers with appropriate permissions
6. **Enable 2FA**: For any admin accounts in your application

## Past Security Advisories

No security advisories have been published yet.

---

Thank you for helping keep this project and its users safe!
