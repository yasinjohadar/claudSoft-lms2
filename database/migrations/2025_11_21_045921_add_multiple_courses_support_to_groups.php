<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Create pivot table for many-to-many relationship
        Schema::create('course_group_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('course_groups')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint to prevent duplicate entries
            $table->unique(['group_id', 'course_id']);

            // Indexes
            $table->index('group_id');
            $table->index('course_id');
        });

        // Step 2: Migrate existing data from course_groups.course_id to pivot table
        $groups = DB::table('course_groups')->whereNotNull('course_id')->get();
        foreach ($groups as $group) {
            DB::table('course_group_courses')->insert([
                'group_id' => $group->id,
                'course_id' => $group->course_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Step 3: Remove course_id column from course_groups table
        Schema::table('course_groups', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropIndex(['course_id']);
            $table->dropColumn('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Re-add course_id column to course_groups
        Schema::table('course_groups', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('id')->constrained('courses')->onDelete('cascade');
            $table->index('course_id');
        });

        // Step 2: Migrate data back from pivot table (take first course only)
        $pivots = DB::table('course_group_courses')
            ->select('group_id', DB::raw('MIN(course_id) as course_id'))
            ->groupBy('group_id')
            ->get();

        foreach ($pivots as $pivot) {
            DB::table('course_groups')
                ->where('id', $pivot->group_id)
                ->update(['course_id' => $pivot->course_id]);
        }

        // Step 3: Drop pivot table
        Schema::dropIfExists('course_group_courses');
    }
};
