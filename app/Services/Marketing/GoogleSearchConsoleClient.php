<?php

namespace App\Services\Marketing;

use App\Models\GoogleSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class GoogleSearchConsoleClient
{
    protected const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    public function __construct(
        protected GoogleSetting $settings,
        protected GoogleServiceAccountTokenService $tokenService
    ) {}

    public function isConfigured(): bool
    {
        return $this->settings->isSearchConsoleApiActive();
    }

    protected function credentials(): array
    {
        $credentials = json_decode($this->settings->service_account_json, true);
        if (! is_array($credentials)) {
            throw new \RuntimeException('Service Account JSON غير صالح');
        }

        return $credentials;
    }

    protected function token(): string
    {
        return $this->tokenService->getAccessToken($this->credentials(), [self::SCOPE]);
    }

    protected function query(array $body): array
    {
        $siteUrl = rawurlencode($this->settings->gsc_site_url);
        $url = "https://www.googleapis.com/webmasters/v3/sites/{$siteUrl}/searchAnalytics/query";

        $response = Http::withToken($this->token())
            ->timeout(30)
            ->post($url, $body);

        if (! $response->successful()) {
            throw new \RuntimeException('Search Console API: ' . $response->body());
        }

        return $response->json();
    }

    public function fetchOverview(int $days = 30): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->query([
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);

        $row = $response['rows'][0] ?? null;

        return [
            'clicks' => (int) ($row['clicks'] ?? 0),
            'impressions' => (int) ($row['impressions'] ?? 0),
            'ctr' => round((float) ($row['ctr'] ?? 0) * 100, 2),
            'position' => round((float) ($row['position'] ?? 0), 1),
        ];
    }

    public function fetchDailyMetrics(int $days = 30): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->query([
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'dimensions' => ['date'],
        ]);

        $labels = [];
        $clicks = [];
        $impressions = [];

        foreach ($response['rows'] ?? [] as $row) {
            $labels[] = Carbon::parse($row['keys'][0])->format('m/d');
            $clicks[] = (int) $row['clicks'];
            $impressions[] = (int) $row['impressions'];
        }

        return compact('labels', 'clicks', 'impressions');
    }

    public function fetchTopQueries(int $days = 30, int $limit = 10): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->query([
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'dimensions' => ['query'],
            'rowLimit' => $limit,
        ]);

        $rows = [];

        foreach ($response['rows'] ?? [] as $row) {
            $rows[] = [
                'query' => $row['keys'][0],
                'clicks' => (int) $row['clicks'],
                'impressions' => (int) $row['impressions'],
                'ctr' => round((float) $row['ctr'] * 100, 2),
                'position' => round((float) $row['position'], 1),
            ];
        }

        return $rows;
    }

    public function fetchTopPages(int $days = 30, int $limit = 10): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->query([
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'dimensions' => ['page'],
            'rowLimit' => $limit,
        ]);

        $rows = [];

        foreach ($response['rows'] ?? [] as $row) {
            $rows[] = [
                'page' => $row['keys'][0],
                'clicks' => (int) $row['clicks'],
                'impressions' => (int) $row['impressions'],
            ];
        }

        return $rows;
    }

    public function ping(): void
    {
        $this->fetchOverview(7);
    }
}
