<?php

use App\Models\Leaderboard;
use App\Models\User;
use Spatie\Permission\Models\Role;

function lbAdmin(): User
{
    Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

test('admin can access leaderboards index', function () {
    $response = $this->actingAs(lbAdmin())->get(route('admin.gamification.leaderboards.index'));

    $response->assertSuccessful();
    $response->assertSee('لوحات المتصدرين', false);
});

test('admin can create leaderboard', function () {
    $response = $this->actingAs(lbAdmin())->post(route('admin.gamification.leaderboards.store'), [
        'name' => 'لوحة اختبار',
        'type' => 'global',
        'metric' => 'total_points',
        'period' => 'all_time',
        'is_active' => '1',
        'is_visible' => '1',
    ]);

    $response->assertRedirect(route('admin.gamification.leaderboards.index'));
    expect(Leaderboard::where('name', 'لوحة اختبار')->exists())->toBeTrue();
});

test('admin can update leaderboard rankings', function () {
    $board = Leaderboard::create([
        'name' => 'Update Test',
        'slug' => 'update-test-'.uniqid(),
        'type' => 'global',
        'metric' => 'total_points',
        'period' => 'all_time',
        'is_active' => true,
        'is_visible' => true,
    ]);

    $response = $this->actingAs(lbAdmin())->post(route('admin.gamification.leaderboards.update-data', $board));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('admin can toggle leaderboard active state', function () {
    $board = Leaderboard::create([
        'name' => 'Toggle Test',
        'slug' => 'toggle-'.uniqid(),
        'type' => 'global',
        'metric' => 'total_points',
        'period' => 'all_time',
        'is_active' => true,
        'is_visible' => true,
    ]);

    $this->actingAs(lbAdmin())->post(route('admin.gamification.leaderboards.toggle-active', $board));

    expect($board->fresh()->is_active)->toBeFalse();
});
