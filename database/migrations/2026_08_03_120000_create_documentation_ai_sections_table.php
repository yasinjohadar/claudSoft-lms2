<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_ai_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')
                ->constrained('documentation_ai_generations')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('heading');
            $table->text('brief')->nullable();
            $table->string('status', 16)->default('pending'); // pending|done|failed
            $table->longText('html')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['generation_id', 'position']);
            $table->index(['generation_id', 'status']);
        });

        Schema::table('documentation_ai_generations', function (Blueprint $table) {
            $table->timestamp('heartbeat_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('documentation_ai_generations', function (Blueprint $table) {
            $table->dropColumn('heartbeat_at');
        });

        Schema::dropIfExists('documentation_ai_sections');
    }
};
