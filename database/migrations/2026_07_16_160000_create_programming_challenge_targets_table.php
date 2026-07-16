<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('programming_challenge_targets');

        Schema::create('programming_challenge_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_id')
                ->constrained('programming_challenges')
                ->cascadeOnDelete();
            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();
            $table->foreignId('group_id')
                ->nullable()
                ->constrained('course_groups')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['programming_challenge_id', 'course_id'], 'pch_targets_challenge_course_idx');
            $table->index(['course_id', 'group_id'], 'pch_targets_course_group_idx');
            $table->unique(
                ['programming_challenge_id', 'course_id', 'group_id'],
                'pch_targets_unique'
            );
        });

        $challenges = DB::table('programming_challenges')
            ->whereNotNull('course_id')
            ->select(['id', 'course_id', 'target_group_id', 'created_at', 'updated_at'])
            ->get();

        $now = now();
        foreach ($challenges as $challenge) {
            DB::table('programming_challenge_targets')->insert([
                'programming_challenge_id' => $challenge->id,
                'course_id' => $challenge->course_id,
                'group_id' => $challenge->target_group_id,
                'created_at' => $challenge->created_at ?? $now,
                'updated_at' => $challenge->updated_at ?? $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_targets');
    }
};
