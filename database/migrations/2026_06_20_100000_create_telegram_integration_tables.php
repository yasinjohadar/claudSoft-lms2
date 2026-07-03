<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->string('telegram_chat_id', 64)->nullable()->unique()->after('full_phone');
            }
            if (! Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username', 100)->nullable()->after('telegram_chat_id');
            }
            if (! Schema::hasColumn('users', 'telegram_linked_at')) {
                $table->timestamp('telegram_linked_at')->nullable()->after('telegram_username');
            }
        });

        Schema::create('telegram_message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('telegram_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->text('message_template');
            $table->string('send_type')->default('text');
            $table->string('target_type')->default('students'); // students | group_chat | channel
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('telegram_chat_id')->nullable();
            $table->string('telegram_chat_title')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status')->default('pending');
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('telegram_broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('telegram_broadcasts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('telegram_incoming_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('chat_id', 64);
            $table->string('telegram_username')->nullable();
            $table->bigInteger('update_id')->nullable();
            $table->bigInteger('message_id')->nullable();
            $table->text('text')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['chat_id', 'received_at']);
        });

        Schema::create('telegram_channel_links', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // course | group
            $table->unsignedBigInteger('entity_id');
            $table->string('link_type')->default('group'); // group | channel
            $table->string('telegram_chat_id', 64);
            $table->string('title')->nullable();
            $table->string('invite_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id', 'link_type']);
        });

        Schema::create('telegram_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('phone_number')->nullable();
            $table->string('session_status')->default('disconnected');
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });

        Schema::table('group_registration_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('group_registration_settings', 'telegram_group_link')) {
                $table->string('telegram_group_link')->nullable()->after('whatsapp_group_link');
            }
            if (! Schema::hasColumn('group_registration_settings', 'telegram_chat_id')) {
                $table->string('telegram_chat_id', 64)->nullable()->after('telegram_group_link');
            }
            if (! Schema::hasColumn('group_registration_settings', 'telegram_template_id')) {
                $table->foreignId('telegram_template_id')->nullable()->after('whatsapp_template_id')
                    ->constrained('telegram_message_templates')->nullOnDelete();
            }
            if (! Schema::hasColumn('group_registration_settings', 'send_welcome_telegram')) {
                $table->boolean('send_welcome_telegram')->default(false)->after('send_welcome_whatsapp');
            }
        });

        Schema::table('group_membership_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('group_membership_requests', 'telegram_invite_sent_at')) {
                $table->timestamp('telegram_invite_sent_at')->nullable()->after('whatsapp_invite_sent_at');
            }
            if (! Schema::hasColumn('group_membership_requests', 'telegram_invite_sent_by')) {
                $table->foreignId('telegram_invite_sent_by')->nullable()->after('telegram_invite_sent_at')
                    ->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('notification_user_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_user_preferences', 'telegram_enabled')) {
                $table->boolean('telegram_enabled')->default(true)->after('whatsapp_wapi_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_user_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('notification_user_preferences', 'telegram_enabled')) {
                $table->dropColumn('telegram_enabled');
            }
        });

        Schema::table('group_membership_requests', function (Blueprint $table) {
            if (Schema::hasColumn('group_membership_requests', 'telegram_invite_sent_by')) {
                $table->dropForeign(['telegram_invite_sent_by']);
                $table->dropColumn('telegram_invite_sent_by');
            }
            if (Schema::hasColumn('group_membership_requests', 'telegram_invite_sent_at')) {
                $table->dropColumn('telegram_invite_sent_at');
            }
        });

        Schema::table('group_registration_settings', function (Blueprint $table) {
            foreach (['send_welcome_telegram', 'telegram_template_id', 'telegram_chat_id', 'telegram_group_link'] as $col) {
                if (Schema::hasColumn('group_registration_settings', $col)) {
                    if ($col === 'telegram_template_id') {
                        $table->dropForeign(['telegram_template_id']);
                    }
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('telegram_accounts');
        Schema::dropIfExists('telegram_channel_links');
        Schema::dropIfExists('telegram_incoming_messages');
        Schema::dropIfExists('telegram_broadcast_recipients');
        Schema::dropIfExists('telegram_broadcasts');
        Schema::dropIfExists('telegram_message_templates');

        Schema::table('users', function (Blueprint $table) {
            foreach (['telegram_linked_at', 'telegram_username', 'telegram_chat_id'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
