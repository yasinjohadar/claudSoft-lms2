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
        Schema::table('assignments', function (Blueprint $table) {
            // Resubmission Settings
            $table->boolean('allow_resubmission')->default(false)->after('late_penalty_percentage');
            $table->integer('max_resubmissions')->nullable()->after('allow_resubmission')->comment('Max number of resubmissions allowed, null = unlimited');
            $table->boolean('resubmit_after_grading_only')->default(true)->after('max_resubmissions')->comment('Student can only resubmit after instructor grades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['allow_resubmission', 'max_resubmissions', 'resubmit_after_grading_only']);
        });
    }
};
