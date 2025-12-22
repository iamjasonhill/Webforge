<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DomainMonitorClient
{
    private string $baseUrl;

    private string $apiKey;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('webforge.domain_monitor.url', ''), '/');
        $this->apiKey = config('webforge.domain_monitor.api_key', '');
        $this->timeout = (int) config('webforge.domain_monitor.timeout', 30);
    }

    /**
     * Check if the client is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey);
    }

    /**
     * Get all domains, optionally filtered.
     *
     * @param  array<string, mixed>  $filters  Optional filters: tag, status, platform
     * @return array<int, array<string, mixed>>
     */
    public function getDomains(array $filters = []): array
    {
        $response = $this->request('GET', '/api/domains', $filters);

        return $response['data'] ?? [];
    }

    /**
     * Get a single domain by ID, domain name, or project key.
     *
     * @return array<string, mixed>|null
     */
    public function getDomain(string $identifier): ?array
    {
        $response = $this->request('GET', "/api/domains/{$identifier}");

        return $response['data'] ?? $response;
    }

    /**
     * Get all tags with domain counts.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTags(): array
    {
        $response = $this->request('GET', '/api/tags');

        return $response['data'] ?? [];
    }

    /**
     * Get domains by tag.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDomainsByTag(string $tagId): array
    {
        $response = $this->request('GET', "/api/tags/{$tagId}/domains");

        return $response['data'] ?? [];
    }

    /**
     * Make an HTTP request to Domain Monitor.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    private function request(string $method, string $endpoint, array $query = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception(
                'Domain Monitor is not configured. Set DOMAIN_MONITOR_URL and DOMAIN_MONITOR_API_KEY environment variables.'
            );
        }

        $url = $this->baseUrl . $endpoint;

        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ])
            ->timeout($this->timeout)
            ->when($method === 'GET', fn($http) => $http->get($url, $query))
            ->when($method === 'POST', fn($http) => $http->post($url, $query));

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            throw new \Exception(
                "Domain Monitor API error ({$status}): {$body}"
            );
        }

        return $response->json() ?? [];
    }
}
