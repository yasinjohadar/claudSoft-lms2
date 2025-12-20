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
        Schema::table('student_works', function (Blueprint $table) {
            // إضافة حقول جديدة
            $table->foreignId('course_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            $table->string('category')->default('other')->after('slug'); // project, assignment, creative, research, other
            $table->json('tags')->nullable()->after('description'); // تاجات للبحث
            $table->json('attachments')->nullable()->after('tags'); // ملفات مرفقة (PDF, Word, etc)
            $table->json('gallery')->nullable()->after('attachments'); // معرض صور
            $table->string('github_url')->nullable()->after('website_url');
            $table->string('demo_url')->nullable()->after('github_url');
            $table->text('technologies')->nullable()->after('demo_url'); // التقنيات المستخدمة
            $table->date('completion_date')->nullable()->after('technologies');
            $table->integer('views_count')->default(0)->after('completion_date');
            $table->integer('likes_count')->default(0)->after('views_count');
            $table->decimal('rating', 3, 2)->nullable()->after('likes_count'); // تقييم من المدرس
            $table->text('admin_feedback')->nullable()->after('rating'); // ملاحظات المدرس
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft')->after('admin_feedback');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_works', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'course_id',
                'category',
                'tags',
                'attachments',
                'gallery',
                'github_url',
                'demo_url',
                'technologies',
                'completion_date',
                'views_count',
                'likes_count',
                'rating',
                'admin_feedback',
                'status',
                'approved_by',
                'approved_at',
            ]);
        });
    }
};
