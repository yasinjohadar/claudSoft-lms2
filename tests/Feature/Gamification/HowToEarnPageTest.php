<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

function pointsTestStudent(): User
{
    $role = Role::findOrCreate('student', 'web');
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('how to earn page displays earning methods from config', function () {
    $user = pointsTestStudent();

    $response = $this->actingAs($user)
        ->get(route('gamification.points.how-to-earn'));

    $response->assertOk();
    $response->assertSee('إتمام درس');
    $response->assertSee('مشاهدة فيديو');
});

test('points index page displays stats and earning preview', function () {
    $user = pointsTestStudent();

    $response = $this->actingAs($user)
        ->get(route('gamification.points.index'));

    $response->assertOk();
    $response->assertSee('إجمالي النقاط');
    $response->assertSee('طرق كسب النقاط');
});
