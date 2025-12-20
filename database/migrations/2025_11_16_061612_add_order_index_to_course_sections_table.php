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
        Schema::table('course_sections', function (Blueprint $table) {
            // Add order_index column with default value same as sort_order
            $table->integer('order_index')->default(0)->after('sort_order');
        });

        // Copy sort_order values to order_index
        DB::statement('UPDATE course_sections SET order_index = sort_order');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropColumn('order_index');
        });
    }
};
