<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            $table->boolean('is_camp')->default(false)->after('is_visible_for_students');
            $table->decimal('price', 10, 2)->nullable()->after('is_camp');
            $table->date('start_date')->nullable()->after('price');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            $table->dropColumn(['is_camp', 'price', 'start_date', 'end_date']);
        });
    }
};
