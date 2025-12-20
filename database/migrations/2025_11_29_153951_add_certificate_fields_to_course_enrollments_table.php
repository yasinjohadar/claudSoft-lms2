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
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->integer('total_attendance_sessions')->default(0)->after('grade');
            $table->integer('attended_sessions')->default(0)->after('total_attendance_sessions');
            $table->decimal('attendance_percentage', 5, 2)->default(0)->after('attended_sessions');
            $table->boolean('certificate_eligible')->default(false)->after('attendance_percentage');
            $table->timestamp('certificate_issued_at')->nullable()->after('certificate_eligible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'total_attendance_sessions',
                'attended_sessions',
                'attendance_percentage',
                'certificate_eligible',
                'certificate_issued_at',
            ]);
        });
    }
};
