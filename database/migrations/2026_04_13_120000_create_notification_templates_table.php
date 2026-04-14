<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->index();
            $table->string('channel', 30)->index();
            $table->string('locale', 10)->default('ar')->index();
            $table->string('name');
            $table->string('title_template')->nullable();
            $table->text('body_template');
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['event_key', 'channel', 'locale'], 'notify_templates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
