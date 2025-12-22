<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class AuditCommand extends Command
{
    protected $signature = 'audit
        {path? : Path to project to audit (defaults to current directory)}
        {--seo : Run SEO audit only}
        {--performance : Run performance audit only}
        {--accessibility : Run accessibility audit only}';

    protected $description = 'Audit a project for SEO, performance, and best practices';

    public function handle(): int
    {
        $path = $this->argument('path') ?? getcwd();

        info('🔍 WebForge Audit');
        info('=================');
        info("Auditing: {$path}\n");

        $results = [];
        $score = 0;
        $maxScore = 0;

        // Detect project type
        $projectType = $this->detectProjectType($path);
        info("Detected project type: {$projectType}\n");

        // Run audits based on options
        if ($this->option('seo') || !$this->hasAnyOption()) {
            [$seoResults, $seoScore, $seoMax] = $this->auditSeo($path, $projectType);
            $results = array_merge($results, $seoResults);
            $score += $seoScore;
            $maxScore += $seoMax;
        }

        if ($this->option('performance') || !$this->hasAnyOption()) {
            [$perfResults, $perfScore, $perfMax] = $this->auditPerformance($path, $projectType);
            $results = array_merge($results, $perfResults);
            $score += $perfScore;
            $maxScore += $perfMax;
        }

        if ($this->option('accessibility') || !$this->hasAnyOption()) {
            [$a11yResults, $a11yScore, $a11yMax] = $this->auditAccessibility($path, $projectType);
            $results = array_merge($results, $a11yResults);
            $score += $a11yScore;
            $maxScore += $a11yMax;
        }

        // Display results
        if (!empty($results)) {
            table(
                headers: ['Check', 'Status', 'Details'],
                rows: $results
            );
        }

        // Summary
        $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
        info("\n�� Score: {$score}/{$maxScore} ({$percentage}%)");

        if ($percentage >= 90) {
            info('✅ Excellent! Your project follows best practices.');
        } elseif ($percentage >= 70) {
            warning('⚠️  Good, but there\'s room for improvement.');
        } else {
            error('❌ Needs attention. Review the issues above.');
        }

        return self::SUCCESS;
    }

    private function hasAnyOption(): bool
    {
        return $this->option('seo') || $this->option('performance') || $this->option('accessibility');
    }

    private function detectProjectType(string $path): string
    {
        if (file_exists($path . '/artisan')) {
            return 'laravel';
        }
        if (file_exists($path . '/wp-config.php') || file_exists($path . '/wp-content')) {
            return 'wordpress';
        }
        if (file_exists($path . '/astro.config.mjs') || file_exists($path . '/astro.config.js')) {
            return 'astro';
        }
        if (file_exists($path . '/package.json')) {
            return 'node';
        }
        return 'unknown';
    }

    private function auditSeo(string $path, string $projectType): array
    {
        $results = [];
        $score = 0;
        $maxScore = 0;

        // Check for sitemap
        $maxScore += 1;
        if ($this->hasSitemap($path, $projectType)) {
            $results[] = ['Sitemap', '✅ Pass', 'sitemap.xml found'];
            $score += 1;
        } else {
            $results[] = ['Sitemap', '❌ Fail', 'No sitemap.xml found'];
        }

        // Check for robots.txt
        $maxScore += 1;
        if ($this->hasRobots($path, $projectType)) {
            $results[] = ['Robots.txt', '✅ Pass', 'robots.txt found'];
            $score += 1;
        } else {
            $results[] = ['Robots.txt', '❌ Fail', 'No robots.txt found'];
        }

        // Check for meta description component
        $maxScore += 1;
        if ($this->hasMetaComponent($path, $projectType)) {
            $results[] = ['Meta Tags', '✅ Pass', 'SEO meta component found'];
            $score += 1;
        } else {
            $results[] = ['Meta Tags', '⚠️ Warn', 'No SEO meta component found'];
        }

        return [$results, $score, $maxScore];
    }

    private function auditPerformance(string $path, string $projectType): array
    {
        $results = [];
        $score = 0;
        $maxScore = 0;

        // Check for favicon
        $maxScore += 1;
        if ($this->hasFavicon($path, $projectType)) {
            $results[] = ['Favicon', '✅ Pass', 'Favicon found'];
            $score += 1;
        } else {
            $results[] = ['Favicon', '❌ Fail', 'No favicon.ico found in public/'];
        }

        // Check for lazy loading in blade templates
        $maxScore += 1;
        if ($this->hasLazyLoading($path, $projectType)) {
            $results[] = ['Lazy Loading', '✅ Pass', 'loading="lazy" found in templates'];
            $score += 1;
        } else {
            $results[] = ['Lazy Loading', '⚠️ Warn', 'Consider adding loading="lazy" to images'];
        }

        // Check for async/defer scripts
        $maxScore += 1;
        if ($this->hasAsyncScripts($path, $projectType)) {
            $results[] = ['Async Scripts', '✅ Pass', 'Scripts use async/defer'];
            $score += 1;
        } else {
            $results[] = ['Async Scripts', '⚠️ Warn', 'Consider adding async/defer to scripts'];
        }

        return [$results, $score, $maxScore];
    }

    private function auditAccessibility(string $path, string $projectType): array
    {
        $results = [];
        $score = 0;
        $maxScore = 0;

        // Check for alt attributes on images
        $maxScore += 1;
        if ($this->hasAltAttributes($path, $projectType)) {
            $results[] = ['Alt Attributes', '✅ Pass', 'Images have alt attributes'];
            $score += 1;
        } else {
            $results[] = ['Alt Attributes', '⚠️ Warn', 'Some images may be missing alt attributes'];
        }

        return [$results, $score, $maxScore];
    }

    private function hasSitemap(string $path, string $projectType): bool
    {
        return match ($projectType) {
            'laravel' => file_exists($path . '/public/sitemap.xml')
            || file_exists($path . '/routes/sitemap.php')
            || $this->grepInFile($path . '/routes/web.php', 'sitemap'),
            'wordpress' => true, // Usually handled by plugins
            'astro' => file_exists($path . '/public/sitemap.xml'),
            default => file_exists($path . '/sitemap.xml'),
        };
    }

    private function hasRobots(string $path, string $projectType): bool
    {
        return match ($projectType) {
            'laravel' => file_exists($path . '/public/robots.txt')
            || $this->grepInFile($path . '/routes/web.php', 'robots'),
            'wordpress' => true, // WordPress generates it
            'astro' => file_exists($path . '/public/robots.txt'),
            default => file_exists($path . '/robots.txt'),
        };
    }

    private function hasMetaComponent(string $path, string $projectType): bool
    {
        return match ($projectType) {
            'laravel' => file_exists($path . '/resources/views/components/seo-head.blade.php')
            || file_exists($path . '/app/View/Components/SeoHead.php'),
            'astro' => file_exists($path . '/src/components/SEO.astro'),
            default => false,
        };
    }

    private function grepInFile(string $file, string $needle): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        return str_contains(file_get_contents($file), $needle);
    }

    private function hasFavicon(string $path, string $projectType): bool
    {
        return match ($projectType) {
            'laravel' => file_exists($path . '/public/favicon.ico')
            || file_exists($path . '/public/favicon.svg'),
            'astro' => file_exists($path . '/public/favicon.ico')
            || file_exists($path . '/public/favicon.svg'),
            default => file_exists($path . '/favicon.ico'),
        };
    }

    private function hasLazyLoading(string $path, string $projectType): bool
    {
        return match ($projectType) {
            'laravel' => $this->grepInDirectory($path . '/resources/views', 'loading="lazy"')
            || $this->grepInDirectory($path . '/resources/views', "loading='lazy'"),
            'astro' => $this->grepInDirectory($path . '/src', 'loading="lazy"'),
            default => false,
        };
    }

    private function hasAsyncScripts(string $path, string $projectType): bool
    {
        return match ($projectType) {
            'laravel' => $this->grepInDirectory($path . '/resources/views', 'defer')
            || $this->grepInDirectory($path . '/resources/views', 'async'),
            'astro' => $this->grepInDirectory($path . '/src', 'defer')
            || $this->grepInDirectory($path . '/src', 'async'),
            default => false,
        };
    }

    private function hasAltAttributes(string $path, string $projectType): bool
    {
        // Check if images have alt attributes - look for img tags with alt
        return match ($projectType) {
            'laravel' => $this->grepInDirectory($path . '/resources/views', 'alt="')
            || $this->grepInDirectory($path . '/resources/views', "alt='"),
            'astro' => $this->grepInDirectory($path . '/src', 'alt="'),
            default => false,
        };
    }

    private function grepInDirectory(string $directory, string $needle): bool
    {
        if (!is_dir($directory)) {
            return false;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $this->grepInFile($file->getPathname(), $needle)) {
                return true;
            }
        }

        return false;
    }

    public function schedule(Schedule $schedule): void
    {
        // No scheduling needed
    }
}
