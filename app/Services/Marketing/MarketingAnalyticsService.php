<?php

namespace App\Services\Marketing;

use App\Models\GoogleSetting;
use Illuminate\Support\Facades\Cache;

class MarketingAnalyticsService
{
    protected GoogleSetting $settings;

    protected GoogleAnalyticsDataClient $analyticsClient;

    protected GoogleSearchConsoleClient $searchConsoleClient;

    public function __construct()
    {
        $this->settings = GoogleSetting::getSettings();
        $tokenService = app(GoogleServiceAccountTokenService::class);
        $this->analyticsClient = new GoogleAnalyticsDataClient($this->settings, $tokenService);
        $this->searchConsoleClient = new GoogleSearchConsoleClient($this->settings, $tokenService);
    }

    public function settings(): GoogleSetting
    {
        return $this->settings;
    }

    protected function cacheKey(string $suffix, string $period): string
    {
        $prefix = config('google_marketing.cache.analytics_prefix', 'marketing_analytics');

        return "{$prefix}:{$suffix}:{$period}";
    }

    protected function cacheTtl(): int
    {
        return $this->settings->getAnalyticsCacheMinutes() * 60;
    }

    protected function periodDays(string $period): int
    {
        return (int) (config("google_marketing.periods.{$period}") ?? 30);
    }

    public function getDashboardData(string $period = '30d', bool $forceRefresh = false): array
    {
        $days = $this->periodDays($period);
        $key = $this->cacheKey('dashboard', $period);

        if ($forceRefresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, $this->cacheTtl(), function () use ($days, $period) {
            $data = [
                'period' => $period,
                'generated_at' => now()->toIso8601String(),
                'status' => [
                    'gtm' => $this->settings->isGtmActive(),
                    'gsc' => $this->settings->isSearchConsoleActive(),
                    'analytics_api' => $this->settings->isAnalyticsApiActive(),
                    'gsc_api' => $this->settings->isSearchConsoleApiActive(),
                ],
                'ga4' => null,
                'gsc' => null,
                'errors' => [],
            ];

            if ($this->settings->isAnalyticsApiActive()) {
                try {
                    $data['ga4'] = [
                        'overview' => $this->analyticsClient->fetchOverview($days),
                        'daily_sessions' => $this->analyticsClient->fetchDailySessions($days),
                        'top_pages' => $this->analyticsClient->fetchTopPages($days),
                        'traffic_sources' => $this->analyticsClient->fetchTrafficSources($days),
                        'top_events' => $this->analyticsClient->fetchTopEvents($days),
                    ];
                } catch (\Throwable $e) {
                    $data['errors']['ga4'] = $e->getMessage();
                }
            }

            if ($this->settings->isSearchConsoleApiActive()) {
                try {
                    $data['gsc'] = [
                        'overview' => $this->searchConsoleClient->fetchOverview($days),
                        'daily' => $this->searchConsoleClient->fetchDailyMetrics($days),
                        'top_queries' => $this->searchConsoleClient->fetchTopQueries($days),
                        'top_pages' => $this->searchConsoleClient->fetchTopPages($days),
                    ];
                } catch (\Throwable $e) {
                    $data['errors']['gsc'] = $e->getMessage();
                }
            }

            if (empty($data['errors'])) {
                GoogleSetting::query()->whereKey($this->settings->id)->update([
                    'last_analytics_sync_at' => now(),
                ]);
                GoogleSetting::clearCache();
            }

            return $data;
        });
    }

    public function syncAll(bool $forceRefresh = true): array
    {
        $results = [];

        foreach (array_keys(config('google_marketing.periods', ['30d' => 30])) as $period) {
            $results[$period] = $this->getDashboardData($period, $forceRefresh);
        }

        return $results;
    }

    public function testConnection(): array
    {
        if (! $this->settings->isAnalyticsApiActive()) {
            return [
                'success' => false,
                'message' => 'Analytics API غير مفعّل — فعّله وأدخل Property ID و Service Account JSON',
            ];
        }

        try {
            $this->analyticsClient->ping();

            $message = 'اتصال GA4 Data API ناجح';

            if ($this->settings->isSearchConsoleApiActive()) {
                $this->searchConsoleClient->ping();
                $message .= ' — وSearch Console API ناجح أيضاً';
            } else {
                $message .= ' — (Search Console API غير مكتمل: أضف Site URL)';
            }

            return ['success' => true, 'message' => $message];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'فشل الاتصال: ' . $e->getMessage(),
            ];
        }
    }
}
