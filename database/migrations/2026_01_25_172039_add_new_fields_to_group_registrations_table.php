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
        Schema::table('group_registrations', function (Blueprint $table) {
            $table->enum('commitment_to_training', ['yes', 'no'])->nullable()->after('special_requirements');
            $table->enum('has_sufficient_time', ['yes', 'no'])->nullable()->after('commitment_to_training');
            $table->enum('has_computer', ['yes', 'no'])->nullable()->after('has_sufficient_time');
            $table->enum('computer_experience_level', ['none', 'beginner', 'intermediate', 'good', 'advanced'])->nullable()->after('has_computer');
            $table->enum('programming_experience', ['none', 'beginner', 'intermediate', 'expert'])->nullable()->after('computer_experience_level');
            $table->text('computer_programming_background')->nullable()->after('programming_experience');
            $table->string('education_level')->nullable()->after('computer_programming_background');
            $table->string('education_major')->nullable()->after('education_level');
            $table->string('current_job')->nullable()->after('education_major');
            $table->enum('interested_in_bootcamp', ['yes', 'no'])->nullable()->after('current_job');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'commitment_to_training',
                'has_sufficient_time',
                'has_computer',
                'computer_experience_level',
                'programming_experience',
                'computer_programming_background',
                'education_level',
                'education_major',
                'current_job',
                'interested_in_bootcamp',
            ]);
        });
    }
};
