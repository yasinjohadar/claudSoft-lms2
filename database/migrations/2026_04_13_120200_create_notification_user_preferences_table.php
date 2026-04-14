<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('event_key')->index();
            $table->boolean('database_enabled')->default(true);
            $table->boolean('realtime_enabled')->default(true);
            $table->boolean('fcm_enabled')->default(true);
            $table->boolean('mail_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event_key'], 'notify_user_pref_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_user_preferences');
    }
};
