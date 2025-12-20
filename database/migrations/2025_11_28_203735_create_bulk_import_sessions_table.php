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
        Schema::create('bulk_import_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path', 500);

            // إحصائيات
            $table->integer('total_rows')->default(0);
            $table->integer('new_users')->default(0);
            $table->integer('updated_users')->default(0);
            $table->integer('skipped_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('enrollments_created')->default(0);
            $table->integer('group_members_added')->default(0);

            // تفاصيل (JSON)
            $table->json('updated_users_details')->nullable(); // قائمة الطلاب المُحدَّثين
            $table->json('new_users_details')->nullable();     // قائمة الطلاب الجدد
            $table->json('errors')->nullable();                // قائمة الأخطاء
            $table->json('mapping')->nullable();               // الـ column mapping

            // حالة
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('uploaded_by');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_import_sessions');
    }
};
