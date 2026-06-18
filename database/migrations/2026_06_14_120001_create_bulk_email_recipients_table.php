<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('bulk_email_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('rendered_subject')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_email_recipients');
    }
};
