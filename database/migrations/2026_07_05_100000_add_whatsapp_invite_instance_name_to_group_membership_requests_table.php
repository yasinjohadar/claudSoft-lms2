<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_membership_requests', function (Blueprint $table) {
            $table->string('whatsapp_invite_instance_name', 150)
                ->nullable()
                ->after('whatsapp_invite_sent_by');
        });
    }

    public function down(): void
    {
        Schema::table('group_membership_requests', function (Blueprint $table) {
            $table->dropColumn('whatsapp_invite_instance_name');
        });
    }
};
