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
        Schema::create('module_access_restrictions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('module_id')->constrained('course_modules')->onDelete('cascade');

            // Restriction Details
            $table->enum('restriction_type', ['user', 'group', 'role'])->comment('Type of restriction');
            $table->unsignedBigInteger('restriction_id')->comment('ID of user/group/role');
            $table->enum('access_type', ['allow', 'deny'])->default('allow');

            $table->timestamps();

            // Indexes
            $table->index('module_id');
            $table->index(['restriction_type', 'restriction_id'], 'mod_access_rest_type_id_idx');
            $table->index('access_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_access_restrictions');
    }
};
