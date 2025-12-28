<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class DocsUpdateCommand extends Command
{
    protected $signature = 'docs:update
        {--path= : Path to the project (defaults to current directory)}
        {--force : Overwrite existing files without confirmation}';

    protected $description = 'Update project documentation from master source';

    /**
     * Master documentation sources.
     * Add new documents here as they become available.
     */
    private array $masterDocs = [
        'PROJECT-CHECKLIST.md' => 'https://raw.githubusercontent.com/iamjasonhill/thebrain/main/brain-client/web/PROJECT-CHECKLIST.md',
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

        foreach ($this->masterDocs as $filename => $url) {
            $destPath = $docsPath . '/' . $filename;
            $exists = file_exists($destPath);

            if ($exists && !$this->option('force')) {
                if (!confirm("Overwrite existing {$filename}?", true)) {
                    warning("  ⏭️  Skipped: {$filename}");
                    continue;
                }
            }

            $result = spin(
                callback: fn() => $this->fetchAndSave($url, $destPath),
                message: "Fetching {$filename}..."
            );

            if ($result) {
                info("  ✓ Updated: {$filename}");
                $updated++;
            } else {
                error("  ✗ Failed: {$filename}");
                $failed++;
            }
        }

        info("\n" . ($updated > 0 ? "✅ Updated {$updated} document(s)" : "No documents updated"));

        if ($failed > 0) {
            warning("{$failed} document(s) failed to update");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function fetchAndSave(string $url, string $destPath): bool
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                return false;
            }

            $content = $response->body();

            // Add update timestamp
            $timestamp = now()->format('Y-m-d H:i:s');
            $content .= "\n\n---\n_Last synced: {$timestamp} via `webforge docs:update`_\n";

            file_put_contents($destPath, $content);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
