<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_membership_requests', function (Blueprint $table) {
            $table->timestamp('email_invite_sent_at')->nullable()->after('whatsapp_invite_sent_by');
            $table->foreignId('email_invite_sent_by')->nullable()->after('email_invite_sent_at')
                ->constrained('users')->nullOnDelete();
            $table->index('email_invite_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('group_membership_requests', function (Blueprint $table) {
            $table->dropForeign(['email_invite_sent_by']);
            $table->dropIndex(['email_invite_sent_at']);
            $table->dropColumn(['email_invite_sent_at', 'email_invite_sent_by']);
        });
    }
};
