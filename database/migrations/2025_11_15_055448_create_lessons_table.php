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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->text('objectives')->nullable();

            // Attachments
            $table->json('attachments')->nullable();

            // Visibility & Publishing
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();

            // Settings
            $table->boolean('allow_comments')->default(true);
            $table->integer('reading_time')->nullable()->comment('minutes');
            $table->integer('sort_order')->default(0);

            // Auditing
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_published');
            $table->index('is_visible');
            $table->index('sort_order');
            $table->index(['available_from', 'available_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
