<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_submission_links')) {
            return;
        }

        Schema::create('project_submission_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_stage_submission_id')->constrained('project_stage_submissions', 'id', 'pc_slinks_submission_fk')->cascadeOnDelete();
            $table->string('link_type', 50);
            $table->string('title')->nullable();
            $table->string('url', 2048);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_stage_submission_id', 'sort_order'], 'pc_slinks_submission_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_submission_links');
    }
};
