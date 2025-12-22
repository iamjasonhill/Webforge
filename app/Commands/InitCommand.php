<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
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
        {--with-brain : Include Brain Nucleus client}
        {--with-seo : Include SEO components}
        {--skip-install : Skip composer/npm install (for testing)}';

    protected $description = 'Initialize a new web project with best practices';

    private string $templatesPath;

    public function __construct()
    {
        parent::__construct();
        $this->templatesPath = base_path('templates');
    }

    public function handle(): int
    {
        info('🔨 WebForge - Project Scaffolder');
        info('================================');

        // Get platform
        $platform = $this->option('platform') ?? select(
            label: 'Which platform would you like to use?',
            options: [
                'laravel' => 'Laravel 12 + Livewire + Tailwind',
                'wordpress' => 'WordPress (CLI-managed)',
                'astro' => 'Astro (Static/SSR)',
            ],
            default: 'laravel'
        );

        // Get project name
        $name = $this->option('name') ?? text(
            label: 'What is your project name?',
            placeholder: 'my-awesome-site',
            required: true
        );

        // Get path
        $defaultPath = getcwd() . '/' . $name;
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
        $withBrain = $this->option('with-brain') ?? confirm(
            label: 'Include Brain Nucleus integration?',
            default: true
        );

        $withSeo = $this->option('with-seo') ?? confirm(
            label: 'Include SEO components?',
            default: true
        );

        info("\n📋 Configuration:");
        info("  Platform: {$platform}");
        info("  Name: {$name}");
        info("  Path: {$path}");
        info("  Brain: " . ($withBrain ? 'Yes' : 'No'));
        info("  SEO: " . ($withSeo ? 'Yes' : 'No'));

        if (!confirm("\nProceed with scaffolding?", true)) {
            warning('Cancelled.');
            return self::FAILURE;
        }

        return match ($platform) {
            'laravel' => $this->scaffoldLaravel($name, $path, $withBrain, $withSeo),
            'wordpress' => $this->scaffoldWordpress($name, $path, $withBrain, $withSeo),
            'astro' => $this->scaffoldAstro($name, $path, $withBrain, $withSeo),
            default => self::FAILURE,
        };
    }

    private function scaffoldLaravel(string $name, string $path, bool $withBrain, bool $withSeo): int
    {
        info("\n🚀 Scaffolding Laravel project...\n");

        $skipInstall = $this->option('skip-install');

        // Step 1: Create Laravel project
        if (!$skipInstall) {
            $result = spin(
                callback: fn() => $this->runCommand(['composer', 'create-project', 'laravel/laravel', $path, '--prefer-dist', '--no-interaction']),
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
                callback: fn() => $this->runCommand(['composer', 'require', 'laravel/breeze', '--dev'], $path),
                message: 'Installing Laravel Breeze...'
            );

            spin(
                callback: fn() => $this->runCommand(['php', 'artisan', 'breeze:install', 'livewire', '--no-interaction'], $path),
                message: 'Setting up Livewire stack...'
            );
        }

        // Step 3: Install dev dependencies
        if (!$skipInstall) {
            spin(
                callback: fn() => $this->runCommand(['composer', 'require', '--dev', 'larastan/larastan', 'phpstan/phpstan'], $path),
                message: 'Installing PHPStan...'
            );
        }

        // Step 4: Install Brain client if requested
        if ($withBrain && !$skipInstall) {
            spin(
                callback: fn() => $this->runCommand(['composer', 'require', 'brain-nucleus/client'], $path),
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
            
            // Copy SEO config
            $this->copyTemplate('laravel/config/seo.php', $path . '/config/seo.php');

            // Add SEO env vars to .env.example
            $this->appendToFile($path . '/.env.example', "\n# SEO\nSEO_DEFAULT_DESCRIPTION=\"Your site description\"\nSEO_DEFAULT_IMAGE=\nSEO_TWITTER_HANDLE=\nSEO_LOGO=\n");
        }

        // Step 7: Add composer scripts
        info('📝 Adding composer scripts...');
        $this->addComposerScripts($path);

        // Step 8: Add Brain env vars if requested
        if ($withBrain) {
            $this->appendToFile($path . '/.env.example', "\n# Brain Nucleus\nBRAIN_BASE_URL=\nBRAIN_API_KEY=\n");
        }

        // Step 9: NPM install
        if (!$skipInstall) {
            spin(
                callback: fn() => $this->runCommand(['npm', 'install'], $path),
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

    private function scaffoldWordpress(string $name, string $path, bool $withBrain, bool $withSeo): int
    {
        info("\n🚀 Scaffolding WordPress project...");
        warning('WordPress scaffolding not yet implemented. Coming soon!');
        return self::SUCCESS;
    }

    private function scaffoldAstro(string $name, string $path, bool $withBrain, bool $withSeo): int
    {
        info("\n🚀 Scaffolding Astro project...");
        warning('Astro scaffolding not yet implemented. Coming soon!');
        return self::SUCCESS;
    }

    private function runCommand(array $command, ?string $cwd = null): bool
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
