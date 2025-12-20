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
            // JSON field to track extra attempts granted to specific students
            // Structure: {"student_id": extra_attempts_count}
            $table->json('extra_attempts_granted')->nullable()->after('resubmit_after_grading_only')
                ->comment('Track extra resubmission attempts granted to specific students');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('extra_attempts_granted');
        });
    }
};
