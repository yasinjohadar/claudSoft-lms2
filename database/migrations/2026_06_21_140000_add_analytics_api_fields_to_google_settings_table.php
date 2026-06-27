<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_settings', function (Blueprint $table) {
            $table->string('ga4_property_id')->nullable()->after('search_console_enabled');
            $table->string('gsc_site_url')->nullable()->after('ga4_property_id');
            $table->text('service_account_json')->nullable()->after('gsc_site_url');
            $table->boolean('analytics_api_enabled')->default(false)->after('service_account_json');
            $table->unsignedSmallInteger('analytics_cache_minutes')->default(60)->after('analytics_api_enabled');
            $table->timestamp('last_analytics_sync_at')->nullable()->after('analytics_cache_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('google_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ga4_property_id',
                'gsc_site_url',
                'service_account_json',
                'analytics_api_enabled',
                'analytics_cache_minutes',
                'last_analytics_sync_at',
            ]);
        });
    }
};
