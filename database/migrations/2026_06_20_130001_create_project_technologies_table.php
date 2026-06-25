<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_technologies')) {
            return;
        }

        Schema::create('project_technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active', 'pc_tech_active_idx');
            $table->index('category', 'pc_tech_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_technologies');
    }
};
