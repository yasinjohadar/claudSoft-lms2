<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenge_test_cases')) {
            return;
        }

        Schema::create('programming_challenge_test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_id')->constrained('programming_challenges', 'id', 'pc_test_cases_ch_fk')->cascadeOnDelete();
            $table->text('input')->nullable();
            $table->text('expected_output')->nullable();
            $table->boolean('is_hidden')->default(true);
            $table->decimal('points', 10, 2)->default(0);
            $table->unsignedInteger('timeout_ms')->default(5000);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_test_cases');
    }
};