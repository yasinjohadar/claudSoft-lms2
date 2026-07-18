<?php

return [
    'enabled' => env('SESSION_ACTIVITY_TRACKING_ENABLED', true),

    'dedup_seconds' => (int) env('SESSION_ACTIVITY_DEDUP_SECONDS', 30),

    'cache_ttl_seconds' => (int) env('SESSION_ACTIVITY_DEDUP_SECONDS', 30) + 5,

    'unknown_type_warning_threshold' => (int) env('SESSION_ACTIVITY_UNKNOWN_WARNING_THRESHOLD', 20),

    'unknown_type_warning_window_seconds' => 60,

    'skip_if_recent' => [
        'focus_lost',
        'focus_gained',
        'disconnect',
        'reconnect',
    ],

    'update_if_recent' => [
        'idle_start',
        'idle_end',
    ],

    'always_insert' => [
        'action',
        'lesson_open',
        'lesson_complete',
        'video_start',
        'video_complete',
        'quiz_start',
        'quiz_submit',
        'file_download',
    ],

    'server_only' => [
        'session_start',
        'session_end',
    ],

    'middleware_only' => [
        'page_view',
    ],
];
