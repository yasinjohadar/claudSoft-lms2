<?php

namespace App\Services\Marketing;

use App\Models\GoogleSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class GoogleAnalyticsDataClient
{
    protected const SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';

    public function __construct(
        protected GoogleSetting $settings,
        protected GoogleServiceAccountTokenService $tokenService
    ) {}

    public function isConfigured(): bool
    {
        return $this->settings->isAnalyticsApiActive();
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

    protected function runReport(array $body): array
    {
        $propertyId = preg_replace('/\D/', '', (string) $this->settings->ga4_property_id);
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runReport";

        $response = Http::withToken($this->token())
            ->timeout(30)
            ->post($url, $body);

        if (! $response->successful()) {
            throw new \RuntimeException('GA4 API: ' . $response->body());
        }

        return $response->json();
    }

    protected function metricValue(array $response, int $index = 0): int|float
    {
        $rows = $response['rows'] ?? [];
        if (empty($rows)) {
            return 0;
        }

        return (float) ($rows[0]['metricValues'][$index]['value'] ?? 0);
    }

    public function fetchOverview(int $days = 30): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->runReport([
            'dateRanges' => [
                ['startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')],
            ],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'totalUsers'],
                ['name' => 'screenPageViews'],
                ['name' => 'engagementRate'],
            ],
        ]);

        return [
            'sessions' => (int) $this->metricValue($response, 0),
            'users' => (int) $this->metricValue($response, 1),
            'page_views' => (int) $this->metricValue($response, 2),
            'engagement_rate' => round((float) $this->metricValue($response, 3) * 100, 1),
        ];
    }

    public function fetchDailySessions(int $days = 30): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->runReport([
            'dateRanges' => [
                ['startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')],
            ],
            'dimensions' => [['name' => 'date']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [
                ['dimension' => ['dimensionName' => 'date']],
            ],
        ]);

        $labels = [];
        $values = [];

        foreach ($response['rows'] ?? [] as $row) {
            $date = $row['dimensionValues'][0]['value'];
            $labels[] = Carbon::createFromFormat('Ymd', $date)->format('m/d');
            $values[] = (int) ($row['metricValues'][0]['value'] ?? 0);
        }

        return compact('labels', 'values');
    }

    public function fetchTopPages(int $days = 30, int $limit = 10): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->runReport([
            'dateRanges' => [
                ['startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')],
            ],
            'dimensions' => [['name' => 'pagePath']],
            'metrics' => [['name' => 'screenPageViews']],
            'orderBys' => [
                ['metric' => ['metricName' => 'screenPageViews'], 'desc' => true],
            ],
            'limit' => $limit,
        ]);

        $rows = [];

        foreach ($response['rows'] ?? [] as $row) {
            $rows[] = [
                'path' => $row['dimensionValues'][0]['value'],
                'views' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ];
        }

        return $rows;
    }

    public function fetchTrafficSources(int $days = 30, int $limit = 8): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->runReport([
            'dateRanges' => [
                ['startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')],
            ],
            'dimensions' => [['name' => 'sessionDefaultChannelGroup']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [
                ['metric' => ['metricName' => 'sessions'], 'desc' => true],
            ],
            'limit' => $limit,
        ]);

        $rows = [];

        foreach ($response['rows'] ?? [] as $row) {
            $rows[] = [
                'source' => $row['dimensionValues'][0]['value'],
                'sessions' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ];
        }

        return $rows;
    }

    public function fetchTopEvents(int $days = 30, int $limit = 8): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $response = $this->runReport([
            'dateRanges' => [
                ['startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')],
            ],
            'dimensions' => [['name' => 'eventName']],
            'metrics' => [['name' => 'eventCount']],
            'orderBys' => [
                ['metric' => ['metricName' => 'eventCount'], 'desc' => true],
            ],
            'limit' => $limit,
        ]);

        $rows = [];

        foreach ($response['rows'] ?? [] as $row) {
            $rows[] = [
                'event' => $row['dimensionValues'][0]['value'],
                'count' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ];
        }

        return $rows;
    }

    public function ping(): void
    {
        $this->fetchOverview(7);
    }
}
