<?php

namespace App\Commands;

use App\Services\DomainMonitorClient;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class InitCommand extends Command
{
    protected $signature = 'init
        {--platform= : Platform to scaffold (laravel, wordpress, astro)}
        {--name= : Project name}
        {--path= : Path to create project (defaults to current directory)}
        {--with-seo : Include SEO components}
        {--skip-install : Skip composer/npm install (for testing)}
        {--from-domain : Select from Domain Monitor domains interactively}
        {--domain= : Initialize from a specific domain in Domain Monitor}';

    protected $description = 'Initialize a new web project with best practices';

    private string $templatesPath;

    /** @var array<string, mixed>|null */
    private ?array $domainData = null;

    public function __construct()
    {
        parent::__construct();
        $this->templatesPath = base_path('templates');
    }

    public function handle(DomainMonitorClient $client): int
    {
        info('🔨 WebForge - Project Scaffolder');
        info('================================');

        // Check if we should fetch from Domain Monitor
        if ($this->option('from-domain') || $this->option('domain')) {
            $this->domainData = $this->fetchDomainData($client);
            if ($this->domainData === null && $this->option('domain')) {
                error('Domain not found in Domain Monitor: ' . $this->option('domain'));

                return self::FAILURE;
            }
        }

        // Get platform (pre-fill from domain if available)
        $defaultPlatform = $this->getDefaultPlatform();
        $platform = $this->option('platform') ?? select(
            label: 'Which platform would you like to use?',
            options: [
                'laravel' => 'Laravel 12 + Livewire + Tailwind',
                'astro' => 'Astro (Static/SSR)',
                'static-php' => 'Static PHP (Simple includes)',
                'wordpress' => 'WordPress (CLI-managed)',
            ],
            default: $defaultPlatform
        );

        // Get project name (pre-fill from domain if available)
        $defaultName = $this->domainData['project_key']
            ?? $this->domainData['domain']
            ?? null;
        $name = $this->option('name') ?? text(
            label: 'What is your project name?',
            placeholder: 'my-awesome-site',
            default: $defaultName ?? '',
            required: true
        );

        // Get path
        $defaultPath = '/Users/jasonhill/Projects/Websites Control Folder/' . $name;
        $path = $this->option('path') ?? text(
            label: 'Where should we create the project?',
            default: $defaultPath,
            required: true
        );

        // Expand ~ to home directory
        if (str_starts_with($path, '~')) {
            $path = $_SERVER['HOME'] . substr($path, 1);
        }

        // Convert to absolute path if relative
        if (!str_starts_with($path, '/')) {
            $path = getcwd() . '/' . $path;
        }

        // Options
        // Brain Nucleus client is always installed for supported platforms

        $withSeo = $this->option('with-seo') ?? confirm(
            label: 'Include SEO components?',
            default: true
        );

        info("\n📋 Configuration:");
        info("  Platform: {$platform}");
        info("  Name: {$name}");
        info("  Path: {$path}");
        info('  SEO: ' . ($withSeo ? 'Yes' : 'No'));
        info('  Brain: Yes (always included)');

        if ($this->domainData) {
            info('  Domain: ' . $this->domainData['domain']);
        }

        if (!$this->option('no-interaction') && !confirm("\nProceed with scaffolding?", true)) {
            warning('Cancelled.');

            return self::FAILURE;
        }

        // Create projects directory if needed
        if (str_starts_with($path, 'projects/')) {
            $projectDir = base_path('projects');
            if (!is_dir($projectDir)) {
                mkdir($projectDir, 0755, true);
            }
        }

        return match ($platform) {
            'laravel' => $this->scaffoldLaravel($name, $path, $withSeo),
            'astro' => $this->scaffoldAstro($name, $path, $withSeo),
            'static-php' => $this->scaffoldStaticPhp($name, $path, $withSeo),
            'wordpress' => $this->scaffoldWordpress($name, $path, $withSeo),
            default => self::FAILURE,
        };
    }

    /**
     * Fetch domain data from Domain Monitor.
     *
     * @return array<string, mixed>|null
     */
    private function fetchDomainData(DomainMonitorClient $client): ?array
    {
        if (!$client->isConfigured()) {
            warning('Domain Monitor is not configured. Skipping domain lookup.');
            info('Set DOMAIN_MONITOR_URL and DOMAIN_MONITOR_API_KEY to enable.');

            return null;
        }

        // Direct domain lookup
        if ($domain = $this->option('domain')) {
            try {
                return $client->getDomain($domain);
            } catch (\Exception $e) {
                warning('Failed to fetch domain: ' . $e->getMessage());

                return null;
            }
        }

        // Interactive domain selection
        try {
            $domains = $client->getDomains(['status' => 'active']);

            if (empty($domains)) {
                warning('No domains found in Domain Monitor.');

                return null;
            }

            $options = [];
            foreach ($domains as $d) {
                $name = $d['name'] ?? $d['domain'] ?? 'Unknown';
                $platform = $d['metadata']['platform'] ?? 'Unknown';
                $options[$name] = "{$name} ({$platform})";
            }

            $selected = search(
                label: 'Select a domain from Domain Monitor:',
                options: fn(string $value) => strlen($value) > 0
                ? collect($options)->filter(fn($label) => str_contains(strtolower($label), strtolower($value)))->all()
                : $options,
                placeholder: 'Type to search domains...'
            );

            if ($selected) {
                return $client->getDomain($selected);
            }
        } catch (\Exception $e) {
            warning('Failed to fetch domains: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get the default platform based on domain data.
     */
    private function getDefaultPlatform(): string
    {
        if (!$this->domainData) {
            return 'laravel';
        }

        $platform = $this->domainData['platform']['type']
            ?? $this->domainData['metadata']['platform']
            ?? null;

        if (!$platform) {
            return 'laravel';
        }

        $platform = strtolower($platform);

        return match (true) {
            str_contains($platform, 'wordpress') => 'wordpress',
            str_contains($platform, 'astro') => 'astro',
            str_contains($platform, 'laravel') => 'laravel',
            str_contains($platform, 'php') => 'static-php',
            default => 'laravel',
        };
    }


    private function scaffoldLaravel(string $name, string $path, bool $withSeo): int
    {
        info("\n🚀 Scaffolding Laravel project...\n");

        $skipInstall = $this->option('skip-install');

        // Step 1: Create Laravel project
        if (!$skipInstall) {
            $result = spin(
                callback: fn() => $this->executeProcess(['composer', 'create-project', 'laravel/laravel', $path, '--prefer-dist', '--no-interaction']),
                message: 'Creating Laravel project...'
            );

            if (!$result) {
                error('Failed to create Laravel project');
                return self::FAILURE;
            }
        } else {
            info('⏭️  Skipping Laravel installation (--skip-install)');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Step 2: Install Breeze with Livewire
        if (!$skipInstall) {
            spin(
                callback: fn() => $this->executeProcess(['composer', 'require', 'laravel/breeze', '--dev'], $path),
                message: 'Installing Laravel Breeze...'
            );

            spin(
                callback: fn() => $this->executeProcess(['php', 'artisan', 'breeze:install', 'livewire', '--no-interaction'], $path),
                message: 'Setting up Livewire stack...'
            );
        }

        // Step 3: Install dev dependencies
        if (!$skipInstall) {
            spin(
                callback: fn() => $this->executeProcess(['composer', 'require', '--dev', 'larastan/larastan', 'phpstan/phpstan'], $path),
                message: 'Installing PHPStan...'
            );
        }

        // Step 4: Install Brain Nucleus client (always included)
        if (!$skipInstall) {
            spin(
                callback: fn() => $this->executeProcess(['composer', 'require', 'brain-nucleus/client'], $path),
                message: 'Installing Brain Nucleus client...'
            );
        }

        // Step 5: Copy config files
        info('📄 Copying configuration files...');
        $this->copyTemplate('laravel/config/pint.json', $path . '/pint.json');
        $this->copyTemplate('laravel/config/phpstan.neon', $path . '/phpstan.neon');

        // Step 6: Copy SEO components if requested
        if ($withSeo) {
            info('🔍 Setting up SEO components...');

            // Create components directory
            $componentsPath = $path . '/resources/views/components';
            if (!is_dir($componentsPath)) {
                mkdir($componentsPath, 0755, true);
            }

            $this->copyTemplate('laravel/components/seo-head.blade.php', $componentsPath . '/seo-head.blade.php');
            $this->copyTemplate('laravel/components/json-ld.blade.php', $componentsPath . '/json-ld.blade.php');
            $this->copyTemplate('laravel/components/image.blade.php', $componentsPath . '/image.blade.php');
            $this->copyTemplate('laravel/components/breadcrumbs.blade.php', $componentsPath . '/breadcrumbs.blade.php');
            $this->copyTemplate('laravel/components/analytics.blade.php', $componentsPath . '/analytics.blade.php');

            // Copy SEO config
            $this->copyTemplate('laravel/config/seo.php', $path . '/config/seo.php');

            // Copy robots.txt and manifest
            $this->copyTemplate('laravel/public/robots.txt', $path . '/public/robots.txt');
            $this->copyTemplate('laravel/public/manifest.json', $path . '/public/manifest.json');

            // Copy sitemap view
            $this->copyTemplate('laravel/views/sitemap.blade.php', $path . '/resources/views/sitemap.blade.php');

            // Copy error pages
            $errorsPath = $path . '/resources/views/errors';
            if (!is_dir($errorsPath)) {
                mkdir($errorsPath, 0755, true);
            }
            $this->copyTemplate('laravel/errors/404.blade.php', $errorsPath . '/404.blade.php');
            $this->copyTemplate('laravel/errors/500.blade.php', $errorsPath . '/500.blade.php');

            // Copy security middleware
            $middlewarePath = $path . '/app/Http/Middleware';
            if (is_dir($middlewarePath)) {
                $this->copyTemplate('laravel/middleware/SecurityHeaders.php', $middlewarePath . '/SecurityHeaders.php');
            }

            // Append sitemap route to web.php
            $this->appendToFile($path . '/routes/web.php', file_get_contents($this->templatesPath . '/laravel/routes/sitemap-route.php'));

            // Add SEO env vars to .env.example
            $this->appendToFile($path . '/.env.example', "\n# SEO\nSEO_DEFAULT_DESCRIPTION=\"Your site description\"\nSEO_DEFAULT_IMAGE=\nSEO_TWITTER_HANDLE=\nSEO_LOGO=\n");

            // Add analytics env var
            $this->appendToFile($path . '/.env.example', "\n# Analytics\nGOOGLE_ANALYTICS_ID=\n");
        }

        // Step 7: Copy CI/CD workflow
        info('🔧 Setting up CI/CD...');
        $workflowsPath = $path . '/.github/workflows';
        if (!is_dir($workflowsPath)) {
            mkdir($workflowsPath, 0755, true);
        }
        $this->copyTemplate('laravel/workflows/ci.yml', $workflowsPath . '/ci.yml');

        // Step 8: Copy pre-commit hook
        info('🪝 Setting up pre-commit hook...');
        $hooksPath = $path . '/.git/hooks';
        if (is_dir($hooksPath)) {
            $this->copyTemplate('laravel/scripts/pre-commit', $hooksPath . '/pre-commit');
            chmod($hooksPath . '/pre-commit', 0755);
        }

        // Step 9: Add composer scripts
        info('📝 Adding composer scripts...');
        $this->addComposerScripts($path);

        // Step 10: Add Brain env vars (always included)
        $this->appendToFile($path . '/.env.example', "\n# Brain Nucleus\nBRAIN_BASE_URL=\nBRAIN_API_KEY=\n");

        // Step 11: NPM install
        if (!$skipInstall) {
            spin(
                callback: fn() => $this->executeProcess(['npm', 'install'], $path),
                message: 'Installing NPM dependencies...'
            );
        }

        // Done!
        info("\n✅ Laravel project scaffolded successfully!\n");
        info("📁 Location: {$path}");
        info("\n🚀 Next steps:");
        info("   cd {$path}");
        info("   cp .env.example .env");
        info("   php artisan key:generate");
        info("   composer dev");

        return self::SUCCESS;
    }

    private function scaffoldStaticPhp(string $name, string $path, bool $withSeo): int
    {
        info("\n🚀 Scaffolding Static PHP project...\n");

        // Create project directory
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // Copy core files
        info('📄 Copying templates...');
        $this->copyTemplate('static-php/config.php', $path . '/config.php');
        $this->copyTemplate('static-php/index.php', $path . '/index.php');
        $this->copyTemplate('static-php/404.php', $path . '/404.php');
        $this->copyTemplate('static-php/.htaccess', $path . '/.htaccess');
        $this->copyTemplate('static-php/robots.txt', $path . '/robots.txt');

        // Create partials directory and copy partials
        $partialsPath = $path . '/partials';
        if (!is_dir($partialsPath)) {
            mkdir($partialsPath, 0755, true);
        }

        $this->copyTemplate('static-php/partials/head.php', $partialsPath . '/head.php');
        $this->copyTemplate('static-php/partials/header.php', $partialsPath . '/header.php');
        $this->copyTemplate('static-php/partials/footer.php', $partialsPath . '/footer.php');
        $this->copyTemplate('static-php/partials/schema.php', $partialsPath . '/schema.php');
        $this->copyTemplate('static-php/partials/analytics.php', $partialsPath . '/analytics.php');

        // Create CSS directory
        $cssPath = $path . '/css';
        if (!is_dir($cssPath)) {
            mkdir($cssPath, 0755, true);
        }
        file_put_contents($cssPath . '/style.css', "/* Your styles here */\n");

        // Create images directory
        $imagesPath = $path . '/images';
        if (!is_dir($imagesPath)) {
            mkdir($imagesPath, 0755, true);
        }

        // Update config with project name
        $configPath = $path . '/config.php';
        $config = file_get_contents($configPath);
        $config = str_replace("'My Site'", "'" . addslashes($name) . "'", $config);
        file_put_contents($configPath, $config);

        // Done!
        info("\n✅ Static PHP project scaffolded successfully!\n");
        info("📁 Location: {$path}");
        info("\n📂 Structure:");
        info("   ├── config.php (edit your settings)");
        info("   ├── index.php (example page)");
        info("   ├── 404.php");
        info("   ├── .htaccess (security headers)");
        info("   ├── partials/");
        info("   │   ├── head.php (SEO meta)");
        info("   │   ├── header.php");
        info("   │   ├── footer.php");
        info("   │   ├── schema.php (JSON-LD)");
        info("   │   └── analytics.php");
        info("   └── css/style.css");
        info("\n🚀 Upload to any PHP host to get started!");

        // TODO: Brain Nucleus Integration
        // Once Brain provides a standalone brain-client.php file, copy it:
        // $this->copyTemplate('static-php/brain-client.php', $path . '/brain-client.php');
        // And add env vars to a sample config or .htaccess

        return self::SUCCESS;
    }

    private function scaffoldWordpress(string $name, string $path, bool $withSeo): int
    {
        info("\n🚀 Scaffolding WordPress project...");
        warning('WordPress scaffolding not yet implemented. Coming soon!');
        return self::SUCCESS;
    }

    private function scaffoldAstro(string $name, string $path, bool $withSeo): int
    {
        info("\n🚀 Scaffolding Astro project...\n");

        $skipInstall = $this->option('skip-install');

        // Step 1: Create Astro project
        if (!$skipInstall) {
            $result = spin(
                callback: fn() => $this->executeProcess(['npm', 'create', 'astro@latest', $path, '--', '--template=minimal', '--install', '--no-git', '-y'], null),
                message: 'Creating Astro project...'
            );

            if (!$result) {
                error('Failed to create Astro project');
                return self::FAILURE;
            }

            // Install additional dependencies
            spin(
                callback: fn() => $this->executeProcess(['npm', 'install', '@astrojs/tailwind', '@astrojs/sitemap', 'eslint', 'eslint-plugin-astro', 'typescript-eslint', 'prettier', 'prettier-plugin-astro', '@fontsource/roboto', 'husky', '@tailwindcss/typography', 'sharp', 'astro-icon', 'vitest', 'happy-dom'], $path),
                message: 'Installing dependencies...'
            );
        } else {
            info('⏭️  Skipping Astro installation (--skip-install)');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Step 2: Copy config files
        info('📄 Copying configuration files...');
        $this->copyTemplate('astro/astro.config.mjs', $path . '/astro.config.mjs');
        $this->copyTemplate('astro/tailwind.config.mjs', $path . '/tailwind.config.mjs');
        $this->copyTemplate('astro/tsconfig.json', $path . '/tsconfig.json');
        $this->copyTemplate('astro/.prettierrc', $path . '/.prettierrc');
        $this->copyTemplate('astro/eslint.config.js', $path . '/eslint.config.js');
        $this->copyTemplate('astro/vitest.config.ts', $path . '/vitest.config.ts');
        $this->copyTemplate('astro/.env.example', $path . '/.env.example');

        // Step 3: Copy SEO components if requested
        if ($withSeo) {
            info('🔍 Setting up SEO components...');

            $componentsPath = $path . '/src/components';
            if (!is_dir($componentsPath)) {
                mkdir($componentsPath, 0755, true);
            }

            $this->copyTemplate('astro/src/components/SEO.astro', $componentsPath . '/SEO.astro');
            $this->copyTemplate('astro/src/components/Schema.astro', $componentsPath . '/Schema.astro');
            $this->copyTemplate('astro/src/components/Breadcrumbs.astro', $componentsPath . '/Breadcrumbs.astro');
            $this->copyTemplate('astro/src/components/Analytics.astro', $componentsPath . '/Analytics.astro');

            // Copy layouts
            $layoutsPath = $path . '/src/layouts';
            if (!is_dir($layoutsPath)) {
                mkdir($layoutsPath, 0755, true);
            }
            $this->copyTemplate('astro/src/layouts/Layout.astro', $layoutsPath . '/Layout.astro');

            // Copy pages
            $pagesPath = $path . '/src/pages';
            if (!is_dir($pagesPath)) {
                mkdir($pagesPath, 0755, true);
            }
            $this->copyTemplate('astro/src/pages/404.astro', $pagesPath . '/404.astro');
            $this->copyTemplate('astro/src/pages/index.astro', $pagesPath . '/index.astro');

            // Replace Title
            if (file_exists($pagesPath . '/index.astro')) {
                $content = file_get_contents($pagesPath . '/index.astro');
                $content = str_replace('New Webforge Project', $name, $content);
                file_put_contents($pagesPath . '/index.astro', $content);
            }

            // Generate dynamic README
            $readmeTemplate = file_get_contents(__DIR__ . '/../../templates/astro/README.md');
            $readmeContent = str_replace('{{ name }}', $name, $readmeTemplate);
            file_put_contents($path . '/README.md', $readmeContent);

            // Copy dynamic robots.txt
            $this->copyTemplate('astro/src/pages/robots.txt.ts', $path . '/src/pages/robots.txt.ts');

            // Copy public files
            $this->copyTemplate('astro/public/manifest.json', $path . '/public/manifest.json');
        }

        // Step 4: Copy deployment configs
        info('🚀 Setting up deployment configs...');
        $this->copyTemplate('astro/vercel.json', $path . '/vercel.json');
        $this->copyTemplate('astro/netlify.toml', $path . '/netlify.toml');

        // Step 5: Copy CI workflow
        info('🔧 Setting up CI/CD...');
        $workflowsPath = $path . '/.github/workflows';
        if (!is_dir($workflowsPath)) {
            mkdir($workflowsPath, 0755, true);
        }
        $this->copyTemplate('astro/.github/workflows/ci.yml', $workflowsPath . '/ci.yml');

        // Step 6: Copy project documentation
        info('📚 Setting up project docs...');
        $docsPath = $path . '/docs';
        if (!is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }
        $this->copyTemplate('astro/docs/PROJECT-CHECKLIST.md', $docsPath . '/PROJECT-CHECKLIST.md');

        // Step 7: Add npm scripts
        $this->addAstroNpmScripts($path);

        // TODO: Brain Nucleus Integration
        // Once brain-nucleus npm package is published, add:
        // spin(
        //     callback: fn() => $this->executeProcess(['npm', 'install', '@brain-nucleus/client'], $path),
        //     message: 'Installing Brain Nucleus client...'
        // );
        // And update .env.example with BRAIN_BASE_URL and BRAIN_API_KEY

        // Done!
        info("\n✅ Astro project scaffolded successfully!\n");
        info("📁 Location: {$path}");
        info("\n🚀 Next steps:");
        info("   cd {$path}");
        info("   cp .env.example .env");
        info("   npm run dev");

        return self::SUCCESS;
    }

    private function addAstroNpmScripts(string $path): void
    {
        $packagePath = $path . '/package.json';

        if (!file_exists($packagePath)) {
            return;
        }

        $package = json_decode(file_get_contents($packagePath), true);

        $package['scripts']['lint'] = 'eslint .';
        $package['scripts']['lint:fix'] = 'eslint . --fix';
        $package['scripts']['format'] = 'prettier --write .';
        $package['scripts']['format:check'] = 'prettier --check .';
        $package['scripts']['prepare'] = 'husky';
        $package['scripts']['test'] = 'vitest';
        $package['scripts']['test:run'] = 'vitest run';

        file_put_contents($packagePath, json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        info("  ✓ Added npm scripts");
    }

    private function executeProcess(array $command, ?string $cwd = null): bool
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(300); // 5 minutes
        $process->run();

        return $process->isSuccessful();
    }

    private function copyTemplate(string $templatePath, string $destPath): void
    {
        $sourcePath = $this->templatesPath . '/' . $templatePath;

        if (file_exists($sourcePath)) {
            // Ensure directory exists
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            copy($sourcePath, $destPath);
            info("  ✓ Created: " . basename($destPath));
        } else {
            warning("  ⚠ Template not found: {$templatePath}");
        }
    }

    private function appendToFile(string $filePath, string $content): void
    {
        if (file_exists($filePath)) {
            file_put_contents($filePath, $content, FILE_APPEND);
        }
    }

    private function addComposerScripts(string $path): void
    {
        $composerPath = $path . '/composer.json';

        if (!file_exists($composerPath)) {
            return;
        }

        $composer = json_decode(file_get_contents($composerPath), true);

        $composer['scripts']['dev'] = [
            'Composer\\Config::disableProcessTimeout',
            'npx concurrently -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" "php artisan serve" "php artisan queue:listen --tries=1" "php artisan pail --timeout=0" "npm run dev" --names=server,queue,logs,vite --kill-others'
        ];

        $composer['scripts']['analyse'] = [
            './vendor/bin/phpstan analyse --memory-limit=2G'
        ];

        $composer['scripts']['check'] = [
            '@php artisan config:clear --ansi',
            './vendor/bin/pint --test',
            './vendor/bin/phpstan analyse --memory-limit=2G',
            '@php artisan test'
        ];

        file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        info("  ✓ Added composer scripts");
    }

    public function schedule(Schedule $schedule): void
    {
        // No scheduling needed
    }
}
