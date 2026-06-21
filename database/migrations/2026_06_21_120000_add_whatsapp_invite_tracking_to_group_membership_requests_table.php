<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_membership_requests', function (Blueprint $table) {
            $table->timestamp('whatsapp_invite_sent_at')->nullable()->after('rejected_by');
            $table->foreignId('whatsapp_invite_sent_by')->nullable()->after('whatsapp_invite_sent_at')
                ->constrained('users')->nullOnDelete();
            $table->index('whatsapp_invite_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('group_membership_requests', function (Blueprint $table) {
            $table->dropForeign(['whatsapp_invite_sent_by']);
            $table->dropIndex(['whatsapp_invite_sent_at']);
            $table->dropColumn(['whatsapp_invite_sent_at', 'whatsapp_invite_sent_by']);
        });
    }
};
