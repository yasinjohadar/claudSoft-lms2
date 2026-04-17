<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wapi_template_variable_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wapi_template_id')->nullable()->constrained('wapi_templates')->nullOnDelete();
            $table->json('variables');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wapi_template_variable_logs');
    }
};
