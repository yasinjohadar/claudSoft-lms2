<?php

namespace App\Services\WhatsApp\Evolution;

use App\Models\EvolutionInstance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EvolutionInstanceRotator
{
    private const CACHE_INDEX_KEY = 'evolution_rotation_index';

    private const LOCK_KEY = 'evolution_rotation_lock';

    public function pool(): Collection
    {
        return EvolutionInstance::rotationEligible()->orderBy('id')->get();
    }

    public function poolCount(): int
    {
        return $this->pool()->count();
    }

    public function nextInstance(): EvolutionInstance
    {
        $pool = $this->pool();

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
    public function orderedPoolForFailover(): Collection
    {
        $pool = $this->pool();

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

    private function reserveNextIndex(): int
    {
        return (int) Cache::lock(self::LOCK_KEY, 5)->block(3, function () {
            $current = (int) Cache::get(self::CACHE_INDEX_KEY, 0);
            Cache::put(self::CACHE_INDEX_KEY, $current + 1, now()->addYear());

            return $current;
        });
    }
}
