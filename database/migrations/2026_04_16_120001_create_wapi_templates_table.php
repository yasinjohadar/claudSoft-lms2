<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wapi_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('language', 24)->default('en_US');
            $table->json('structure')->nullable();
            $table->string('provider_template_id')->nullable();
            $table->timestamps();

            $table->unique(['name', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wapi_templates');
    }
};
