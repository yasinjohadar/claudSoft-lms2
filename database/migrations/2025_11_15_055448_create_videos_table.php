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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();

            // Video Details
            $table->enum('video_type', ['upload', 'youtube', 'vimeo', 'external'])->default('upload');
            $table->string('video_url', 500)->nullable();
            $table->string('video_path')->nullable();
            $table->string('thumbnail')->nullable();
            $table->integer('duration')->nullable()->comment('seconds');
            $table->json('quality')->nullable();
            $table->json('subtitles')->nullable();

            // Processing Status
            $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('processing_error')->nullable();

            // Visibility & Publishing
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();

            // Settings
            $table->boolean('allow_download')->default(false);
            $table->boolean('allow_speed_control')->default(true);
            $table->boolean('require_watch_complete')->default(false);
            $table->integer('sort_order')->default(0);

            // Auditing
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_published');
            $table->index('is_visible');
            $table->index('processing_status');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
