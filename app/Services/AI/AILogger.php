<?php

namespace App\Services\AI;

use App\Models\AIRequest;
use Illuminate\Support\Facades\Log;

class AILogger
{
    /**
     * Log AI request
     *
     * @param AIRequest $request
     * @param array $response
     * @return void
     */
    public function logRequest(AIRequest $request, array $response): void
    {
        Log::info('AI Request', [
            'request_id' => $request->id,
            'provider_id' => $request->provider_id,
            'user_id' => $request->user_id,
            'request_type' => $request->request_type,
            'status' => $request->status,
            'tokens_used' => $request->tokens_used,
            'cost' => $request->cost,
            'response_time_ms' => $request->response_time_ms,
        ]);
    }

    /**
     * Log AI error
     *
     * @param AIRequest $request
     * @param \Exception $exception
     * @return void
     */
    public function logError(AIRequest $request, \Exception $exception): void
    {
        Log::error('AI Request Failed', [
            'request_id' => $request->id,
            'provider_id' => $request->provider_id,
            'user_id' => $request->user_id,
            'request_type' => $request->request_type,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Get usage statistics
     *
     * @param int|null $userId
     * @param string|null $requestType
     * @param \Carbon\Carbon|null $startDate
     * @param \Carbon\Carbon|null $endDate
     * @return array
     */
    public function getUsageStats(
        ?int $userId = null,
        ?string $requestType = null,
        ?\Carbon\Carbon $startDate = null,
        ?\Carbon\Carbon $endDate = null
    ): array {
        $query = AIRequest::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($requestType) {
            $query->where('request_type', $requestType);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $totalRequests = $query->count();
        $completedRequests = (clone $query)->where('status', 'completed')->count();
        $failedRequests = (clone $query)->where('status', 'failed')->count();
        $totalTokens = (clone $query)->sum('tokens_used');
        $totalCost = (clone $query)->sum('cost');
        $avgResponseTime = (clone $query)->whereNotNull('response_time_ms')->avg('response_time_ms');

        return [
            'total_requests' => $totalRequests,
            'completed_requests' => $completedRequests,
            'failed_requests' => $failedRequests,
            'success_rate' => $totalRequests > 0 ? ($completedRequests / $totalRequests) * 100 : 0,
            'total_tokens' => $totalTokens,
            'total_cost' => $totalCost,
            'average_response_time_ms' => round($avgResponseTime ?? 0, 2),
        ];
    }

    /**
     * Get cost statistics by provider
     *
     * @param \Carbon\Carbon|null $startDate
     * @param \Carbon\Carbon|null $endDate
     * @return array
     */
    public function getCostByProvider(?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): array
    {
        $query = AIRequest::with('provider')
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get()
            ->groupBy('provider_id')
            ->map(function ($requests, $providerId) {
                $provider = $requests->first()->provider;
                return [
                    'provider_id' => $providerId,
                    'provider_name' => $provider->name ?? 'Unknown',
                    'total_requests' => $requests->count(),
                    'total_tokens' => $requests->sum('tokens_used'),
                    'total_cost' => $requests->sum('cost'),
                    'average_cost_per_request' => $requests->avg('cost'),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Check if cost threshold is exceeded
     *
     * @param float $threshold
     * @param \Carbon\Carbon|null $startDate
     * @param \Carbon\Carbon|null $endDate
     * @return bool
     */
    public function isCostThresholdExceeded(
        float $threshold,
        ?\Carbon\Carbon $startDate = null,
        ?\Carbon\Carbon $endDate = null
    ): bool {
        $query = AIRequest::where('status', 'completed');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $totalCost = $query->sum('cost');

        if ($totalCost >= $threshold) {
            Log::warning('AI Cost Threshold Exceeded', [
                'threshold' => $threshold,
                'total_cost' => $totalCost,
            ]);
            return true;
        }

        return false;
    }
}

