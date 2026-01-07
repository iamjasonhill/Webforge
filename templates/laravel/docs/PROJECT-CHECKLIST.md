# Project Checklist - Laravel Application

> **Source of Truth** for all Laravel projects. Copy to your project using `webforge init` or manually.

---

## Phase 0: Pre-Project Discovery

> Complete BEFORE starting any code work.

### Questionnaire

**Business & Brand**
- [ ] Business name confirmed?
- [ ] Tagline/value proposition defined?
- [ ] Primary brand colors hex codes?
- [ ] Logo available in SVG/PNG?
- [ ] Target audience defined?

**Technical Scope**
- [ ] Database requirements (SQLite vs MySQL/PostgreSQL)?
- [ ] Hosting environment (Forge / Vapor / VPS)?
- [ ] Mail service provider (Mailgun / SES / Postmark)?
- [ ] Queue driver requirements (Database / Redis / SQS)?
- [ ] Cache driver requirements (File / Redis)?

### Domain & DNS
- [ ] Production domain confirmed?
- [ ] DNS access confirmed?
- [ ] SSL requirements?

---

## Phase 1: Environment & Setup

- [ ] Repository created and cloned
- [ ] `composer setup` run successfully
- [ ] `.env` configured:
  - [ ] `APP_NAME` set
  - [ ] `APP_URL` set
  - [ ] `DB_CONNECTION` verified
  - [ ] `MAIL_*` settings configured
- [ ] Application Key generated (`php artisan key:generate`)
- [ ] Storage linked (`php artisan storage:link`)
- [ ] Database migrated (`php artisan migrate`)

---

## Phase 2: Branding & UI

- [ ] **Favicon**: Replace `public/favicon.ico`
- [ ] **Logo**: Update `resources/views/components/application-logo.blade.php`
- [ ] **Colors**: Update `tailwind.config.js` / `resources/css/app.css` with brand colors
- [ ] **Name**: Update `config/app.php` name if not using ENV

---

## Phase 3: Core Features & AI

### Laravel Boost (AI)
- [ ] Verify installation (`composer require laravel/boost --dev`)
- [ ] Run setup (`php artisan boost:install`)
- [ ] Index codebase (`php artisan boost:scan`)
- [ ] Review `llms.txt` (or create one in `public/`)

### Authentication & Users
- [ ] Verify Login/Register flows (Breeze)
- [ ] Create initial admin user
- [ ] Verify Password Reset flow (Mail config required)

### Analytics (Brain Nucleus)
- [ ] Configure `BRAIN_API_KEY` in `.env`
- [ ] Verify client connection

---

## Phase 4: Development Standards

- [ ] **Code Quality**: Run `composer check` often
  - [ ] Pint (Formatting)
  - [ ] PHPStan (Static Analysis)
  - [ ] Tests (PHPUnit)
- [ ] **Pre-commit Hook**: Verify it blocks invalid styling
- [ ] **Queues**: Ensure `php artisan queue:listen` acts as expected (run via `composer dev`)

---

## Phase 5: Deployment Preparation

### Production Config
- [ ] `Nginx` / `Apache` configuration prepared (if managing manually)
- [ ] `Supervisor` configuration for queues
- [ ] `Cron` entry for scheduler (`* * * * * php /path/to/artisan schedule:run`)

### Security Check
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Permissions correct (storage/cache writable)
- [ ] Security headers middleware enabled (if applicable)

---

## Phase 6: Post-Launch
- [ ] Verify backup system is active
- [ ] Monitor logs (`storage/logs/laravel.log` or Pail)
- [ ] Check Brain Nucleus dashboard for analytics
- [ ] Verify email delivery
