<?php

namespace Tests\Feature\Admin;

use App\Ai\Agents\PingAgent;
use App\Models\LaravelAiModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaravelAiModelAdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_create_laravel_ai_model(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('admin.ai-sdk.models.store'), [
            'name' => 'Test OpenAI',
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-test-key',
            'base_url' => null,
            'is_active' => '1',
            'priority' => 10,
            'capabilities' => ['blog.generate'],
            'max_tokens' => 2048,
            'temperature' => 0.5,
        ]);

        $response->assertRedirect(route('admin.ai-sdk.models.index'));
        $this->assertDatabaseHas('laravel_ai_models', [
            'name' => 'Test OpenAI',
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'is_active' => true,
            'priority' => 10,
        ]);
    }

    public function test_connection_test_uses_fake_agent(): void
    {
        $user = $this->adminUser();

        $model = new LaravelAiModel([
            'name' => 'Fake model',
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'is_active' => true,
            'priority' => 0,
            'max_tokens' => 1000,
            'temperature' => 0.5,
        ]);
        $model->setRawApiKeyForTesting('sk-fake');
        $model->save();

        PingAgent::fake(['OK']);

        $response = $this->actingAs($user)
            ->postJson(route('admin.ai-sdk.models.test', $model));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'latency_ms']);

        $this->assertDatabaseHas('laravel_ai_logs', [
            'laravel_ai_model_id' => $model->id,
            'operation' => 'connection.test',
            'status' => 'success',
        ]);
    }
}
