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
        Schema::create('google_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gtm_container_id')->nullable();
            $table->boolean('gtm_enabled')->default(false);
            $table->string('search_console_verification')->nullable();
            $table->boolean('search_console_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_settings');
    }
};
