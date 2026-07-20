<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->foreignId('bunny_stream_library_id')
                ->nullable()
                ->after('embed_code')
                ->constrained('bunny_stream_libraries')
                ->nullOnDelete();

            $table->index('bunny_stream_library_id');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bunny_stream_library_id');
        });
    }
};
