<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('leaderboard_id')->constrained('leaderboards')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Ranking
            $table->integer('rank')->unsigned()->comment('الترتيب');
            $table->integer('previous_rank')->unsigned()->nullable()->comment('الترتيب السابق');
            $table->integer('rank_change')->default(0)->comment('التغيير في الترتيب');

            // Score
            $table->integer('score')->unsigned()->default(0)->comment('النقاط');
            $table->integer('previous_score')->unsigned()->default(0)->comment('النقاط السابقة');

            // Division
            $table->enum('division', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])->default('bronze');
            $table->enum('previous_division', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])->nullable();

            // Additional Metrics
            $table->json('metrics')->nullable()->comment('مقاييس إضافية');

            // Status
            $table->boolean('is_qualified')->default(true)->comment('مؤهل للظهور؟');
            $table->timestamp('last_activity_at')->nullable()->comment('آخر نشاط');

            // Achievements
            $table->boolean('is_top_1')->default(false)->comment('المركز الأول');
            $table->boolean('is_top_3')->default(false)->comment('ضمن الثلاثة الأوائل');
            $table->boolean('is_top_10')->default(false)->comment('ضمن العشرة الأوائل');

            $table->timestamps();

            // Indexes
            $table->unique(['leaderboard_id', 'user_id'], 'lb_entry_unique');
            $table->index(['leaderboard_id', 'rank']);
            $table->index('score');
            $table->index('division');
            $table->index('is_top_1');
            $table->index('is_top_3');
            $table->index('is_top_10');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
