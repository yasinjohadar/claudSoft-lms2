<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleSetting;
use App\Services\Marketing\MarketingAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $settings = GoogleSetting::getSettings();

        $stats = [
            'gtm_active' => $settings->isGtmActive(),
            'gsc_active' => $settings->isSearchConsoleActive(),
            'api_active' => $settings->isAnalyticsApiActive(),
            'gsc_api_active' => $settings->isSearchConsoleApiActive(),
            'last_sync' => $settings->last_analytics_sync_at,
        ];

        return view('admin.pages.marketing-analytics.index', compact('settings', 'stats'));
    }

    public function data(Request $request, MarketingAnalyticsService $analytics): JsonResponse
    {
        $period = $request->get('period', '30d');
        if (! array_key_exists($period, config('google_marketing.periods', []))) {
            $period = '30d';
        }

        $forceRefresh = $request->boolean('refresh');

        if ($forceRefresh) {
            $cacheKey = 'marketing_analytics_refresh_' . $request->user()->id;
            if (cache()->has($cacheKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'يرجى الانتظار دقيقة قبل التحديث مرة أخرى',
                ], 429);
            }
            cache()->put($cacheKey, true, 60);
        }

        $data = $analytics->getDashboardData($period, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
