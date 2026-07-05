<?php

namespace App\Services\WhatsApp\Evolution;

use App\Models\EvolutionInstance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EvolutionInstanceRotator
{
    private const CACHE_INDEX_KEY = 'evolution_rotation_index';

    private const LOCK_KEY = 'evolution_rotation_lock';

    private const REFRESH_CACHE_KEY = 'evolution_rotation_pool_refreshed_at';

    public function __construct(
        private EvolutionService $evolutionService,
    ) {}

    public function pool(bool $forceRefresh = false): Collection
    {
        $this->refreshPoolStatusesIfDue($forceRefresh);

        return EvolutionInstance::rotationEligible()->orderBy('id')->get();
    }

    public function poolCount(bool $forceRefresh = false): int
    {
        return $this->pool($forceRefresh)->count();
    }

    public function nextInstance(bool $forceRefresh = false): EvolutionInstance
    {
        $pool = $this->pool($forceRefresh);

        if ($pool->isEmpty()) {
            throw new EvolutionApiException(
                'لا توجد أرقام WhatsApp متصلة ومفعّلة للتبديل. اربط instance واحداً على الأقل من لوحة Evolution.',
                'No rotation-eligible Evolution instances available.',
            );
        }

        $index = $this->reserveNextIndex();
        $position = $index % $pool->count();

        return $pool->values()->get($position);
    }

    /**
     * Pool ordered for failover, starting from the next round-robin pick.
     */
    public function orderedPoolForFailover(bool $forceRefresh = false): Collection
    {
        $pool = $this->pool($forceRefresh);

        if ($pool->isEmpty()) {
            return collect();
        }

        $pool = $pool->values();
        $startIndex = $this->reserveNextIndex() % $pool->count();
        $ordered = collect();

        for ($i = 0; $i < $pool->count(); $i++) {
            $ordered->push($pool->get(($startIndex + $i) % $pool->count()));
        }

        return $ordered;
    }

    public function markUsed(EvolutionInstance $instance): void
    {
        $instance->update(['last_used_at' => now()]);
    }

    private function refreshPoolStatusesIfDue(bool $forceRefresh): void
    {
        if (! $forceRefresh && Cache::get(self::REFRESH_CACHE_KEY)) {
            return;
        }

        $this->evolutionService->refreshRotationCandidates();
        Cache::put(self::REFRESH_CACHE_KEY, true, now()->addSeconds(60));
    }

    private function reserveNextIndex(): int
    {
        return (int) Cache::lock(self::LOCK_KEY, 5)->block(3, function () {
            $current = (int) Cache::get(self::CACHE_INDEX_KEY, 0);
            Cache::put(self::CACHE_INDEX_KEY, $current + 1, now()->addYear());

            return $current;
        });
    }
}
