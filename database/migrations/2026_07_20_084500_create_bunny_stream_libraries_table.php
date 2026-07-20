<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunny_stream_libraries', function (Blueprint $table) {
            $table->id();
            $table->string('library_id')->unique()->comment('Bunny Stream library numeric ID');
            $table->string('library_name');
            $table->text('token_security_key');
            $table->text('api_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunny_stream_libraries');
    }
};
