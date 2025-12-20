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
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();

            // SMTP Configuration
            $table->string('mail_mailer')->default('smtp')->comment('Mail driver (smtp, sendmail, mailgun, etc)');
            $table->string('mail_host')->nullable()->comment('SMTP Host (e.g., smtp.gmail.com)');
            $table->integer('mail_port')->default(587)->comment('SMTP Port (e.g., 587, 465, 25)');
            $table->string('mail_username')->nullable()->comment('SMTP Username/Email');
            $table->text('mail_password')->nullable()->comment('SMTP Password (encrypted)');
            $table->string('mail_encryption')->default('tls')->comment('Encryption type (tls, ssl, null)');

            // From Configuration
            $table->string('mail_from_address')->nullable()->comment('From email address');
            $table->string('mail_from_name')->nullable()->comment('From name');

            // Additional Settings
            $table->boolean('is_active')->default(false)->comment('Is this configuration active?');
            $table->string('provider')->default('custom')->comment('Provider (gmail, outlook, custom, etc)');
            $table->json('test_results')->nullable()->comment('Last test results');
            $table->timestamp('last_tested_at')->nullable()->comment('Last test date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
