<?php

namespace App\Commands;

use App\Services\DomainMonitorClient;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class DomainsCommand extends Command
{
    protected $signature = 'domains
        {--tag= : Filter by tag name}
        {--status= : Filter by status (active/inactive)}
        {--platform= : Filter by platform}
        {--json : Output as JSON}';

    protected $description = 'List domains from Domain Monitor';

    public function handle(DomainMonitorClient $client): int
    {
        if (!$client->isConfigured()) {
            error('Domain Monitor is not configured.');
            info('Set these environment variables:');
            info('  DOMAIN_MONITOR_URL=https://your-domain-monitor.example.com');
            info('  DOMAIN_MONITOR_API_KEY=your-api-key');

            return self::FAILURE;
        }

        try {
            $filters = array_filter([
                'tag' => $this->option('tag'),
                'status' => $this->option('status'),
                'platform' => $this->option('platform'),
            ]);

            $domains = $client->getDomains($filters);

            if (empty($domains)) {
                warning('No domains found.');

                return self::SUCCESS;
            }

            if ($this->option('json')) {
                $this->line(json_encode($domains, JSON_PRETTY_PRINT));

                return self::SUCCESS;
            }

            info('📋 Domains from Domain Monitor');
            info('==============================');

            $rows = [];
            foreach ($domains as $domain) {
                $status = $domain['status'] ?? 'unknown';
                $statusIcon = $status === 'active' ? '✓' : '✗';

                $rows[] = [
                    $domain['name'] ?? $domain['domain'] ?? 'N/A',
                    "{$statusIcon} {$status}",
                    $domain['metadata']['platform'] ?? 'Unknown',
                    $domain['expires_at'] ?? 'N/A',
                ];
            }

            table(
                ['Domain', 'Status', 'Platform', 'Expires'],
                $rows
            );

            info("\nTotal: " . count($domains) . ' domains');

            return self::SUCCESS;
        } catch (\Exception $e) {
            error('Failed to fetch domains: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
