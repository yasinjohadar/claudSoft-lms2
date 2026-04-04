<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->string('resource_scope', 20)->default('general');
            $table->string('classification', 50)->nullable();
            $table->index('resource_scope');
            $table->index('classification');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE resources MODIFY COLUMN resource_type ENUM(
                'pdf','doc','ppt','excel','image','audio','archive','external_sites','other'
            ) NOT NULL DEFAULT 'pdf'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE resources SET resource_type = 'other' WHERE resource_type = 'external_sites'");

            DB::statement("ALTER TABLE resources MODIFY COLUMN resource_type ENUM(
                'pdf','doc','ppt','excel','image','audio','archive','other'
            ) NOT NULL DEFAULT 'pdf'");
        }

        Schema::table('resources', function (Blueprint $table) {
            $table->dropIndex(['classification']);
            $table->dropColumn('classification');
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->dropIndex(['resource_scope']);
            $table->dropColumn('resource_scope');
        });
    }
};
