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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('الطالب');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade')->comment('الوسام');

            // Award Information
            $table->timestamp('awarded_at')->useCurrent()->comment('تاريخ الحصول على الوسام');
            $table->string('reason')->nullable()->comment('سبب منح الوسام');

            // Polymorphic Relation (What triggered the badge)
            $table->morphs('related'); // creates: related_type & related_id
            // Example: related_type = 'App\Models\Course', related_id = 5 (Badge for completing course 5)
            // Example: related_type = 'App\Models\QuizAttempt', related_id = 123

            // Progress & Status
            $table->decimal('progress', 5, 2)->default(100)->comment('نسبة التقدم (100% = تم الحصول عليه)');
            $table->boolean('is_seen')->default(false)->comment('هل شاهد الطالب الإشعار؟');
            $table->boolean('is_featured')->default(false)->comment('هل مميز في الملف الشخصي؟');

            // Points Awarded
            $table->integer('points_awarded')->unsigned()->default(0)->comment('النقاط الممنوحة');

            // Metadata
            $table->json('metadata')->nullable()->comment('بيانات إضافية');

            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'badge_id'], 'user_badge_unique');
            $table->index('awarded_at');
            // morphs() already creates index for related_type & related_id
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
