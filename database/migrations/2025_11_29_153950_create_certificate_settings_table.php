<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificate_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('certificate_settings')->insert([
            [
                'key' => 'certificate_prefix',
                'value' => 'CERT',
                'type' => 'string',
                'description' => 'Certificate number prefix',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_auto_issue',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Automatically issue certificates on course completion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_qr_code',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Include QR code on certificates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_watermark',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Add watermark to certificates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'certificate_language',
                'value' => 'ar',
                'type' => 'string',
                'description' => 'Default certificate language',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'verification_url',
                'value' => url('/verify-certificate'),
                'type' => 'string',
                'description' => 'Certificate verification URL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_settings');
    }
};
