<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_releases', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 20); // android | ios
            $table->string('version_name', 30); // 1.2.0 (يظهر للطالب)
            $table->unsignedInteger('build_number'); // يُقارن مع رقم البناء في التطبيق
            $table->text('release_notes')->nullable();
            $table->string('distribution', 20)->default('store'); // store | direct
            $table->string('download_url', 1000);
            $table->boolean('is_forced')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('notified_at')->nullable();
            $table->unsignedInteger('notified_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['platform', 'build_number']);
            $table->index(['platform', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_releases');
    }
};
