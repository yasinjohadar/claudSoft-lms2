<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('programming_languages', 'monaco_language_id')) {
            Schema::table('programming_languages', function (Blueprint $table) {
                $table->string('monaco_language_id')->nullable()->after('sort_order');
            });
        }

        if (! Schema::hasColumn('programming_languages', 'execution_mode')) {
            Schema::table('programming_languages', function (Blueprint $table) {
                $table->enum('execution_mode', ['none', 'client_web', 'server'])->default('none')->after('monaco_language_id');
            });
        }

        if (! Schema::hasColumn('programming_languages', 'runtime_slug')) {
            Schema::table('programming_languages', function (Blueprint $table) {
                $table->string('runtime_slug')->nullable()->after('execution_mode');
            });
        }

        if (! Schema::hasColumn('programming_languages', 'file_extension')) {
            Schema::table('programming_languages', function (Blueprint $table) {
                $table->string('file_extension', 20)->nullable()->after('runtime_slug');
            });
        }

        if (! Schema::hasColumn('programming_languages', 'default_filename')) {
            Schema::table('programming_languages', function (Blueprint $table) {
                $table->string('default_filename')->nullable()->after('file_extension');
            });
        }
    }

    public function down(): void
    {
        Schema::table('programming_languages', function (Blueprint $table) {
            $columns = ['monaco_language_id', 'execution_mode', 'runtime_slug', 'file_extension', 'default_filename'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('programming_languages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};