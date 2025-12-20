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
        Schema::create('webhook_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم التوكن');
            $table->enum('source', ['wpforms', 'n8n', 'other'])->default('wpforms')->comment('مصدر الـ webhook');
            $table->text('token')->comment('التوكن/المفتاح السري');
            $table->json('allowed_ips')->nullable()->comment('قائمة IPs المسموحة');
            $table->json('form_types')->nullable()->comment('ربط Form IDs بأنواع الإرساليات');
            $table->text('description')->nullable()->comment('وصف التوكن');
            $table->boolean('is_active')->default(true)->comment('حالة التوكن');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_tokens');
    }
};
