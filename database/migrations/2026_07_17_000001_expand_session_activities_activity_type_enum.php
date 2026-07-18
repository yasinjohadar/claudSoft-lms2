<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'session_activities';

    private const ORIGINAL_TYPES = [
        'session_start',
        'session_end',
        'page_view',
        'action',
        'disconnect',
        'reconnect',
        'idle_start',
        'idle_end',
        'focus_lost',
        'focus_gained',
    ];

    private const EXPANDED_TYPES = [
        'session_start',
        'session_end',
        'page_view',
        'action',
        'disconnect',
        'reconnect',
        'idle_start',
        'idle_end',
        'focus_lost',
        'focus_gained',
        'lesson_open',
        'lesson_complete',
        'video_start',
        'video_complete',
        'quiz_start',
        'quiz_submit',
        'file_download',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        $this->modifyEnum(self::EXPANDED_TYPES);
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        $learning = [
            'lesson_open',
            'lesson_complete',
            'video_start',
            'video_complete',
            'quiz_start',
            'quiz_submit',
            'file_download',
        ];

        $hasLearningRows = DB::table(self::TABLE)
            ->whereIn('activity_type', $learning)
            ->exists();

        if ($hasLearningRows) {
            return;
        }

        $this->modifyEnum(self::ORIGINAL_TYPES);
    }

    /**
     * @param  list<string>  $types
     */
    private function modifyEnum(array $types): void
    {
        $quoted = collect($types)
            ->map(fn (string $type) => "'".$type."'")
            ->implode(',');

        DB::statement(
            'ALTER TABLE `'.self::TABLE.'` MODIFY `activity_type` ENUM('.$quoted.') NOT NULL'
        );
    }
};
