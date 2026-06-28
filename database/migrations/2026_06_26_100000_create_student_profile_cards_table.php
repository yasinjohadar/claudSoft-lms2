<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profile_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->json('social_links')->nullable();
            $table->json('theme')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('admin_enabled')->default(true);
            $table->boolean('qr_enabled')->default(true);
            $table->string('qr_code_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profile_cards');
    }
};
