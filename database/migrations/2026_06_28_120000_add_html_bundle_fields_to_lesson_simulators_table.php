<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_simulators', function (Blueprint $table) {
            $table->string('render_mode', 32)->default('html_bundle')->after('spec_version');
            $table->string('simulator_archetype', 32)->nullable()->after('render_mode');
            $table->string('bundle_path')->nullable()->after('simulator_archetype');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_simulators', function (Blueprint $table) {
            $table->dropColumn(['render_mode', 'simulator_archetype', 'bundle_path']);
        });
    }
};
