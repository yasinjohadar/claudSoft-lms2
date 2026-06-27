<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meta_pixel_settings')) {
            return;
        }

        Schema::create('meta_pixel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('pixel_id')->nullable();
            $table->boolean('enabled')->default(false);
            $table->text('capi_access_token')->nullable();
            $table->boolean('capi_enabled')->default(false);
            $table->string('test_event_code')->nullable();
            $table->boolean('track_page_view')->default(true);
            $table->boolean('track_view_content')->default(true);
            $table->boolean('track_search')->default(true);
            $table->boolean('track_lead')->default(true);
            $table->boolean('track_contact')->default(true);
            $table->boolean('track_lead_started')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pixel_settings');
    }
};
