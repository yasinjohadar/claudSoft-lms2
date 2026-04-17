<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wapi_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 32);
            $table->string('type', 32);
            $table->json('content');
            $table->string('status', 48);
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wapi_messages');
    }
};
