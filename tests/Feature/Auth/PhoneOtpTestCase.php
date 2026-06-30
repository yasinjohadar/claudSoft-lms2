<?php

namespace Tests\Feature\Auth;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

abstract class PhoneOtpTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('phone_otp_codes');
        Schema::dropIfExists('wapi_templates');
        Schema::dropIfExists('system_settings');

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['key', 'group']);
        });

        Schema::create('wapi_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('language', 24)->default('en_US');
            $table->json('structure')->nullable();
            $table->string('provider_template_id')->nullable();
            $table->timestamps();
            $table->unique(['name', 'language']);
        });

        Schema::create('phone_otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 32)->index();
            $table->string('purpose', 64)->index();
            $table->string('code_hash');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }
}
