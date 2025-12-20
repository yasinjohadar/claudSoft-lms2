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
        Schema::create('wpforms_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_id'); // WPForms Form ID
            $table->string('entry_id')->nullable(); // WPForms Entry ID
            $table->string('submission_type')->default('enrollment'); // enrollment, contact, review, etc.
            $table->json('form_data'); // Raw form data from WPForms
            $table->string('status')->default('pending'); // pending, processed, failed
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Created user (if applicable)
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); // Related course (if applicable)
            $table->text('processing_notes')->nullable(); // Processing details/errors
            $table->timestamp('processed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'entry_id']);
            $table->index('status');
            $table->index('submission_type');
        });

        // Webhook logs table for debugging
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('wpforms'); // wpforms, other sources
            $table->string('event_type')->nullable(); // form_submit, payment_complete, etc.
            $table->json('payload'); // Full webhook payload
            $table->json('headers')->nullable(); // Request headers
            $table->string('status')->default('received'); // received, processed, failed
            $table->text('response')->nullable(); // Processing response
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['source', 'event_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('wpforms_submissions');
    }
};
