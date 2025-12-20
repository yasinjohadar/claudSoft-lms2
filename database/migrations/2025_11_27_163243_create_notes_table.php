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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('صاحب الملاحظة');
            $table->string('title')->comment('عنوان الملاحظة');
            $table->text('content')->comment('محتوى الملاحظة');
            $table->string('category')->default('personal')->comment('التصنيف: personal, study, work, etc');
            $table->string('color')->default('#3b82f6')->comment('لون الملاحظة');
            $table->boolean('is_pinned')->default(false)->comment('مثبتة في الأعلى');
            $table->boolean('is_favorite')->default(false)->comment('مفضلة');
            $table->boolean('is_archived')->default(false)->comment('مؤرشفة');
            $table->timestamp('reminder_at')->nullable()->comment('تذكير في تاريخ محدد');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('category');
            $table->index('is_pinned');
            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
