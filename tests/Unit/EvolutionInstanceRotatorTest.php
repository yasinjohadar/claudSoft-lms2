<?php

namespace Tests\Unit;

use App\Models\EvolutionInstance;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EvolutionInstanceRotatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_round_robin_cycles_through_connected_instances(): void
    {
        EvolutionInstance::create([
            'instance_name' => 'inst-a',
            'connection_status' => 'open',
            'rotation_enabled' => true,
        ]);
        EvolutionInstance::create([
            'instance_name' => 'inst-b',
            'connection_status' => 'open',
            'rotation_enabled' => true,
        ]);
        EvolutionInstance::create([
            'instance_name' => 'inst-c',
            'connection_status' => 'close',
            'rotation_enabled' => true,
        ]);

        $rotator = app(EvolutionInstanceRotator::class);

        $first = $rotator->nextInstance()->instance_name;
        $second = $rotator->nextInstance()->instance_name;
        $third = $rotator->nextInstance()->instance_name;

        $this->assertContains($first, ['inst-a', 'inst-b']);
        $this->assertContains($second, ['inst-a', 'inst-b']);
        $this->assertNotSame($first, $second);
        $this->assertSame($first, $third);
    }

    public function test_rotation_disabled_instances_are_excluded(): void
    {
        EvolutionInstance::create([
            'instance_name' => 'inst-a',
            'connection_status' => 'open',
            'rotation_enabled' => false,
        ]);
        EvolutionInstance::create([
            'instance_name' => 'inst-b',
            'connection_status' => 'open',
            'rotation_enabled' => true,
        ]);

        $rotator = app(EvolutionInstanceRotator::class);

        $this->assertSame(1, $rotator->poolCount());
        $this->assertSame('inst-b', $rotator->nextInstance()->instance_name);
    }
}
