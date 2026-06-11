<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->string('content_mode', 20)->default('upload');

            $table->string('preview_url')->nullable();
            $table->string('preview_file_path')->nullable();
            $table->string('preview_file_name')->nullable();
            $table->string('preview_mime_type')->nullable();

            $table->string('download_url')->nullable();
            $table->string('download_file_path')->nullable();
            $table->string('download_file_name')->nullable();
            $table->string('download_mime_type')->nullable();
            $table->unsignedBigInteger('download_file_size')->nullable();

            $table->string('target_type', 30)->nullable();
            $table->json('target_payload')->nullable();

            $table->string('status', 20)->default('draft');
            $table->timestamp('granted_at')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_regranted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_gift_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_gift_id')->constrained('student_gifts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('previewed_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->unique(['student_gift_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_gift_recipients');
        Schema::dropIfExists('student_gifts');
    }
};
