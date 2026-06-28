<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('simulator_categories')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'sort_order']);
        });

        Schema::table('lesson_simulators', function (Blueprint $table) {
            $table->foreignId('simulator_category_id')
                ->nullable()
                ->after('topic_key')
                ->constrained('simulator_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lesson_simulators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('simulator_category_id');
        });

        Schema::dropIfExists('simulator_categories');
    }
};
