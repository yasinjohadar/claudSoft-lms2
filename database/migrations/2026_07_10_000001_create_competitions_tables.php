<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->unsignedInteger('target_value')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status')->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'ends_at']);
            $table->index('type');
        });

        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('current_value')->default(0);
            $table->unsignedInteger('rank')->default(1);
            $table->boolean('is_winner')->default(false);
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['competition_id', 'user_id']);
            $table->index(['user_id', 'is_winner']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
        Schema::dropIfExists('competitions');
    }
};
