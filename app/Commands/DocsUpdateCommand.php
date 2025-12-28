<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class DocsUpdateCommand extends Command
{
    protected $signature = 'docs:update
        {--path= : Path to the project (defaults to current directory)}
        {--force : Overwrite existing files without confirmation}';

    protected $description = 'Update project documentation from Webforge templates';

    private string $templatesPath;

    public function __construct()
    {
        parent::__construct();
        $this->templatesPath = base_path('templates');
    }

    /**
     * Documentation files to sync.
     * Maps destination filename => template path
     */
    private array $docs = [
        'PROJECT-CHECKLIST.md' => 'astro/docs/PROJECT-CHECKLIST.md',
    ];

    public function handle(): int
    {
        info('📚 WebForge - Documentation Updater');
        info('===================================');

        $path = $this->option('path') ?? getcwd();

        // Expand ~ to home directory
        if (str_starts_with($path, '~')) {
            $path = $_SERVER['HOME'] . substr($path, 1);
        }

        // Convert to absolute path if relative
        if (!str_starts_with($path, '/')) {
            $path = getcwd() . '/' . $path;
        }

        // Check if docs directory exists
        $docsPath = $path . '/docs';
        if (!is_dir($docsPath)) {
            if (!confirm('Create docs/ directory?', true)) {
                warning('Cancelled.');
                return self::FAILURE;
            }
            mkdir($docsPath, 0755, true);
        }

        info("\n📁 Updating docs in: {$docsPath}\n");

        $updated = 0;
        $failed = 0;

        foreach ($this->docs as $filename => $templatePath) {
            $destPath = $docsPath . '/' . $filename;
            $sourcePath = $this->templatesPath . '/' . $templatePath;
            $exists = file_exists($destPath);

            if (!file_exists($sourcePath)) {
                error("  ✗ Template not found: {$templatePath}");
                $failed++;
                continue;
            }

            if ($exists && !$this->option('force')) {
                if (!confirm("Overwrite existing {$filename}?", true)) {
                    warning("  ⏭️  Skipped: {$filename}");
                    continue;
                }
            }

            // Copy from template
            $content = file_get_contents($sourcePath);

            // Add sync timestamp
            $timestamp = now()->format('Y-m-d H:i:s');
            $content .= "\n\n---\n_Last synced: {$timestamp} via `webforge docs:update`_\n";

            file_put_contents($destPath, $content);
            info("  ✓ Updated: {$filename}");
            $updated++;
        }

        info("\n" . ($updated > 0 ? "✅ Updated {$updated} document(s)" : "No documents updated"));

        if ($failed > 0) {
            warning("{$failed} document(s) failed to update");
        }

        info("\n💡 Tip: Update Webforge to get latest templates: cd " . base_path() . " && git pull");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

