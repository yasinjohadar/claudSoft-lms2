<?php

use App\Models\CourseGroup;
use App\Models\User;
use App\Services\Student\StudentAccountTierService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    Schema::dropIfExists('course_group_members');
    Schema::dropIfExists('course_groups');
    Schema::dropIfExists('activity_log');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->timestamps();
    });

    Schema::create('activity_log', function (Blueprint $table) {
        $table->id();
        $table->string('log_name')->nullable();
        $table->text('description')->nullable();
        $table->nullableMorphs('subject');
        $table->nullableMorphs('causer');
        $table->json('properties')->nullable();
        $table->uuid('batch_uuid')->nullable();
        $table->string('event')->nullable();
        $table->timestamps();
    });

    Schema::create('course_groups', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->boolean('is_camp')->default(false);
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::create('course_group_members', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('group_id');
        $table->unsignedBigInteger('student_id');
        $table->timestamps();
    });
});

test('student in paid camp group is gold', function () {
    $student = User::query()->create(['name' => 'Gold', 'email' => 'gold@test.local']);
    $group = CourseGroup::query()->create(['name' => 'Camp Group', 'is_camp' => true]);
    DB::table('course_group_members')->insert([
        'group_id' => $group->id,
        'student_id' => $student->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new StudentAccountTierService;

    expect($service->isGold($student))->toBeTrue()
        ->and($service->resolve($student))->toBe('gold');
});

test('student only in ordinary group is silver', function () {
    $student = User::query()->create(['name' => 'Silver', 'email' => 'silver@test.local']);
    $group = CourseGroup::query()->create(['name' => 'Ordinary', 'is_camp' => false]);
    DB::table('course_group_members')->insert([
        'group_id' => $group->id,
        'student_id' => $student->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new StudentAccountTierService;

    expect($service->isGold($student))->toBeFalse()
        ->and($service->resolve($student))->toBe('silver');
});

test('student with no group membership is silver', function () {
    $student = User::query()->create(['name' => 'Alone', 'email' => 'alone@test.local']);
    $service = new StudentAccountTierService;

    expect($service->resolve($student))->toBe('silver');
});
