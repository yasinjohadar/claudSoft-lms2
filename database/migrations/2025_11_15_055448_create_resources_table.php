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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();

            // File Details
            $table->enum('resource_type', ['pdf', 'doc', 'ppt', 'excel', 'image', 'audio', 'archive', 'other'])->default('pdf');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable()->comment('bytes');
            $table->string('mime_type', 100)->nullable();

            // Visibility & Publishing
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();

            // Settings
            $table->boolean('allow_download')->default(true);
            $table->boolean('preview_available')->default(false);
            $table->integer('download_count')->default(0);
            $table->integer('sort_order')->default(0);

            // Auditing
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_published');
            $table->index('is_visible');
            $table->index('resource_type');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
