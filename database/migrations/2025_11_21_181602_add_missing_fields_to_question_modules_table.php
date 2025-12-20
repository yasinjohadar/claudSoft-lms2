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
        Schema::table('question_modules', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('title');
            $table->boolean('is_visible')->default(true)->after('is_published');
            $table->boolean('show_results')->default(true)->after('show_feedback');
            $table->timestamp('available_from')->nullable()->after('show_results');
            $table->timestamp('available_until')->nullable()->after('available_from');
            $table->integer('sort_order')->default(0)->after('available_until');
            $table->decimal('pass_percentage', 5, 2)->nullable()->after('passing_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_modules', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'is_visible', 'show_results', 'available_from', 'available_until', 'sort_order', 'pass_percentage']);
        });
    }
};
