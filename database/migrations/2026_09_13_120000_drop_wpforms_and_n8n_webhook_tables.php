<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the WPForms and n8n webhook subsystems.
 *
 * Both were removed from the application: WPForms was a WordPress-only form
 * receiver whose public endpoint created verified users and active enrollments
 * without authentication when no secret was configured, and n8n was a dormant
 * automation bridge with no configured endpoints.
 *
 * down() recreates the tables exactly as the original migrations did, so the
 * schema can be restored; the rows themselves come back from a database backup.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Child first: outgoing_webhook_logs holds an FK to n8n_webhook_endpoints.
        Schema::dropIfExists('outgoing_webhook_logs');
        Schema::dropIfExists('n8n_webhook_endpoints');
        Schema::dropIfExists('n8n_incoming_webhook_handlers');
        Schema::dropIfExists('wpforms_submissions');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhook_tokens');
    }

    public function down(): void
    {
        Schema::create('webhook_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('source', ['wpforms', 'n8n', 'other'])->default('wpforms');
            $table->text('token');
            $table->json('allowed_ips')->nullable();
            $table->json('form_types')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->default('wpforms');
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('status')->default('received');
            $table->text('response')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['source', 'event_type']);
            $table->index('status');
            $table->index(['source', 'status']);
        });

        Schema::create('wpforms_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_id');
            $table->string('entry_id')->nullable();
            $table->string('submission_type')->default('enrollment');
            $table->json('form_data');
            $table->string('status')->default('pending');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->text('processing_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'entry_id']);
            $table->index('status');
            $table->index('submission_type');
        });

        Schema::create('n8n_webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event_type')->index();
            $table->text('url');
            $table->string('secret_key');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedTinyInteger('retry_attempts')->default(3);
            $table->unsignedSmallInteger('timeout')->default(30);
            $table->json('headers')->nullable();
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'is_active']);
        });

        Schema::create('outgoing_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('endpoint_id')->constrained('n8n_webhook_endpoints')->onDelete('cascade');
            $table->string('event_type')->index();
            $table->json('payload');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->enum('status', ['pending', 'sent', 'failed', 'retrying'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['endpoint_id', 'status']);
        });

        Schema::create('n8n_incoming_webhook_handlers', function (Blueprint $table) {
            $table->id();
            $table->string('handler_type')->unique();
            $table->string('handler_class');
            $table->text('description')->nullable();
            $table->json('required_fields')->nullable();
            $table->json('optional_fields')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('example_payload')->nullable();
            $table->timestamps();

            $table->index(['handler_type', 'is_active']);
        });
    }
};
