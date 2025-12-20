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
        Schema::create('course_access_restrictions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Restriction Details
            $table->enum('restriction_type', ['user', 'group', 'role', 'department'])->comment('Type of restriction');
            $table->unsignedBigInteger('restriction_id')->comment('ID of user/group/role/department');
            $table->enum('access_type', ['allow', 'deny'])->default('allow');

            $table->timestamps();

            // Indexes
            $table->index('course_id');
            $table->index(['restriction_type', 'restriction_id'], 'course_access_rest_type_id_idx');
            $table->index('access_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_access_restrictions');
    }
};
