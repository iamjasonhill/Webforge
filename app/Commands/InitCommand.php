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
        $this->copyTemplate('laravel/config/phpstan-baseline.neon', $path . '/phpstan-baseline.neon');

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

        // Step 7: Copy CI/CD workflow & Dependabot
        info('🔧 Setting up CI/CD...');
        $workflowsPath = $path . '/.github/workflows';
        if (!is_dir($workflowsPath)) {
            mkdir($workflowsPath, 0755, true);
        }
        $this->copyTemplate('laravel/.github/workflows/ci.yml', $workflowsPath . '/ci.yml');
        $this->copyTemplate('laravel/.github/dependabot.yml', $path . '/.github/dependabot.yml');

        // Step 8: Copy Documentation
        info('📚 Setting up project documentation...');
        $docs = ['CONTRIBUTING.md', 'CHANGELOG.md', 'SECURITY.md', 'README.md'];
        foreach ($docs as $doc) {
            $this->copyTemplate('laravel/' . $doc, $path . '/' . $doc);
        }
        
        // Replace placeholders in docs and config
        $filesToProcess = [
            'CONTRIBUTING.md', 'CHANGELOG.md', 'SECURITY.md', 'README.md', 
            '.github/dependabot.yml'
        ];
        
        $replacements = [
            '{{PROJECT_NAME}}' => $name,
            '{{REPO_NAME}}' => strtolower(str_replace(' ', '-', $name)),
            '{{GITHUB_USERNAME}}' => 'iamjasonhill',
            '{{AUTHOR_EMAIL}}' => 'jason@example.com',
            '{{CURRENT_DATE}}' => date('Y-m-d'),
        ];

        foreach ($filesToProcess as $file) {
            $filePath = $path . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
                    file_put_contents($filePath, $content);
                }
            }
        }

        // Step 9: Initialize git repository (needed for pre-commit hook)
        info('🔧 Initializing git repository...');
        if (!is_dir($path . '/.git')) {
            spin(
                callback: fn() => $this->executeProcess(['git', 'init'], $path),
                message: 'Creating git repository...'
            );
        }

        // Step 9: Append PHPStan cache to .gitignore
        $gitignoreAdditions = file_get_contents($this->templatesPath . '/laravel/config/gitignore-additions.txt');
        if ($gitignoreAdditions !== false) {
            $this->appendToFile($path . '/.gitignore', "\n" . $gitignoreAdditions);
        }

        // Step 10: Copy pre-commit hook
        info('🪝 Setting up pre-commit hook...');
        $hooksPath = $path . '/.git/hooks';
        if (is_dir($hooksPath)) {
            $this->copyTemplate('laravel/scripts/pre-commit', $hooksPath . '/pre-commit');
            chmod($hooksPath . '/pre-commit', 0755);
        }

        // Step 11: Add composer scripts
        info('📝 Adding composer scripts...');
        $this->addComposerScripts($path);

        // Step 12: Add Brain env vars (always included)
        $this->appendToFile($path . '/.env.example', "\n# Brain Nucleus\nBRAIN_BASE_URL=\nBRAIN_API_KEY=\n");

        // Step 13: NPM install
        if (!$skipInstall) {
            spin(
                callback: fn() => $this->executeProcess(['npm', 'install'], $path),
                message: 'Installing NPM dependencies...'
            );
            spin(
                callback: fn() => $this->executeProcess(['npm', 'install', 'concurrently', '--save-dev'], $path),
                message: 'Installing concurrently...'
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
        try {
            if (file_exists($configPath)) {
                $config = file_get_contents($configPath);
                if ($config !== false) {
                    $config = str_replace("'My Site'", "'" . addslashes($name) . "'", $config);
                    if (file_put_contents($configPath, $config) === false) {
                        warning("  ⚠ Failed to update config.php with project name");
                    }
                }
            }
        } catch (\Exception $e) {
            warning("  ⚠ Error updating config.php: " . $e->getMessage());
        }

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

        // Add Brain env vars to .env.example (always included)
        $this->appendToFile($path . '/.env.example', "\n# Brain Nucleus Analytics\nPUBLIC_BRAIN_URL=\nPUBLIC_BRAIN_KEY=\n");

        // Step 3: Copy source files
        info('📂 Copying project source files...');

        $sourceSrc = $this->templatesPath . '/astro/src';
        $destSrc = $path . '/src';

        if (!File::isDirectory($sourceSrc)) {
            warning("  ⚠ Template src directory not found: {$sourceSrc}");
        } else {
            try {
                File::copyDirectory($sourceSrc, $destSrc);
                info("  ✓ Copied src directory structure");
            } catch (\Exception $e) {
                error("  ✗ Failed to copy src directory: " . $e->getMessage());
            }
        }

        // Copy Brain Analytics component (always included)
        $this->copyTemplate('astro/src/components/BrainAnalytics.astro', $path . '/src/components/BrainAnalytics.astro');

        // Replace Project Name in index.astro
        $indexPath = $destSrc . '/pages/index.astro';
        if (file_exists($indexPath)) {
            try {
                $content = file_get_contents($indexPath);
                if ($content !== false) {
                    $content = str_replace('New Webforge Project', $name, $content);
                    if (file_put_contents($indexPath, $content) === false) {
                        warning("  ⚠ Failed to update project name in index.astro");
                    }
                }
            } catch (\Exception $e) {
                warning("  ⚠ Error updating index.astro: " . $e->getMessage());
            }
        }

        // Generate dynamic README
        try {
            $readmeTemplatePath = __DIR__ . '/../../templates/astro/README.md';
            if (file_exists($readmeTemplatePath)) {
                $readmeTemplate = file_get_contents($readmeTemplatePath);
                if ($readmeTemplate !== false) {
                    $readmeContent = str_replace('{{ name }}', $name, $readmeTemplate);
                    if (file_put_contents($path . '/README.md', $readmeContent) === false) {
                        warning("  ⚠ Failed to create README.md");
                    }
                }
            } else {
                warning("  ⚠ README template not found: {$readmeTemplatePath}");
            }
        } catch (\Exception $e) {
            warning("  ⚠ Error creating README: " . $e->getMessage());
        }

        // Copy dynamic robots.txt
        // (Already copied via recursive src copy if it exists in templates/astro/src/pages)

        // Copy public files
        $this->copyTemplate('astro/public/manifest.json', $path . '/public/manifest.json');
        $this->copyTemplate('astro/public/brain-analytics.js', $path . '/public/brain-analytics.js');

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

        // Brain Nucleus Analytics is included via:
        // - brain-analytics.js (copied to public/)
        // - BrainAnalytics.astro component (copied to src/components/)
        // - Layout.astro includes <BrainAnalytics />
        // - .env.example includes PUBLIC_BRAIN_URL and PUBLIC_BRAIN_KEY

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
            warning("  ⚠ package.json not found: {$packagePath}");
            return;
        }

        try {
            $content = file_get_contents($packagePath);
            if ($content === false) {
                error("  ✗ Failed to read package.json");
                return;
            }

            $package = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error("  ✗ Failed to parse package.json: " . json_last_error_msg());
                return;
            }

            if (!isset($package['scripts'])) {
                $package['scripts'] = [];
            }

            $package['scripts']['lint'] = 'eslint .';
            $package['scripts']['lint:fix'] = 'eslint . --fix';
            $package['scripts']['format'] = 'prettier --write .';
            $package['scripts']['format:check'] = 'prettier --check .';
            $package['scripts']['prepare'] = 'husky';
            $package['scripts']['test'] = 'vitest';
            $package['scripts']['test:run'] = 'vitest run';

            $json = json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            if ($json === false) {
                error("  ✗ Failed to encode package.json: " . json_last_error_msg());
                return;
            }

            if (file_put_contents($packagePath, $json) === false) {
                error("  ✗ Failed to write package.json");
                return;
            }

            info("  ✓ Added npm scripts");
        } catch (\Exception $e) {
            error("  ✗ Error adding npm scripts: " . $e->getMessage());
        }
    }

    private function executeProcess(array $command, ?string $cwd = null): bool
    {
        try {
            $process = new Process($command, $cwd);
            $process->setTimeout(300); // 5 minutes
            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                $output = $process->getOutput();

                warning("Command failed: " . implode(' ', $command));
                if (!empty($errorOutput)) {
                    warning("Error: " . trim($errorOutput));
                }
                if (!empty($output) && empty($errorOutput)) {
                    warning("Output: " . trim($output));
                }

                return false;
            }

            return true;
        } catch (\Exception $e) {
            warning("Exception running command: " . $e->getMessage());
            return false;
        }
    }

    private function copyTemplate(string $templatePath, string $destPath): void
    {
        $sourcePath = $this->templatesPath . '/' . $templatePath;

        if (!file_exists($sourcePath)) {
            warning("  ⚠ Template not found: {$templatePath}");
            return;
        }

        try {
            // Ensure directory exists
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                if (!mkdir($destDir, 0755, true)) {
                    error("  ✗ Failed to create directory: {$destDir}");
                    return;
                }
            }

            if (!copy($sourcePath, $destPath)) {
                error("  ✗ Failed to copy template: {$templatePath} to {$destPath}");
                return;
            }

            info("  ✓ Created: " . basename($destPath));
        } catch (\Exception $e) {
            error("  ✗ Error copying template {$templatePath}: " . $e->getMessage());
        }
    }

    private function appendToFile(string $filePath, string $content): void
    {
        if (!file_exists($filePath)) {
            warning("  ⚠ File not found for appending: {$filePath}");
            return;
        }

        try {
            if (file_put_contents($filePath, $content, FILE_APPEND) === false) {
                error("  ✗ Failed to append to file: {$filePath}");
            }
        } catch (\Exception $e) {
            error("  ✗ Error appending to file {$filePath}: " . $e->getMessage());
        }
    }

    private function addComposerScripts(string $path): void
    {
        $composerPath = $path . '/composer.json';

        if (!file_exists($composerPath)) {
            warning("  ⚠ composer.json not found: {$composerPath}");
            return;
        }

        try {
            $content = file_get_contents($composerPath);
            if ($content === false) {
                error("  ✗ Failed to read composer.json");
                return;
            }

            $composer = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error("  ✗ Failed to parse composer.json: " . json_last_error_msg());
                return;
            }

            $composer['scripts']['setup'] = [
                'composer install',
                '@php -r "file_exists(\'.env\') || copy(\'.env.example\', \'.env\');"',
                '@php artisan key:generate',
                '@php artisan migrate --force',
                'npm install',
                'npm run build'
            ];

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

            $json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            if ($json === false) {
                error("  ✗ Failed to encode composer.json: " . json_last_error_msg());
                return;
            }

            if (file_put_contents($composerPath, $json) === false) {
                error("  ✗ Failed to write composer.json");
                return;
            }

            info("  ✓ Added composer scripts");
        } catch (\Exception $e) {
            error("  ✗ Error adding composer scripts: " . $e->getMessage());
        }
    }

    public function schedule(Schedule $schedule): void
    {
        // No scheduling needed
    }
}
