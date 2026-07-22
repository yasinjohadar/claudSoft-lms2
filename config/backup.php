<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Native dump tools
    |--------------------------------------------------------------------------
    */
    'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH'), // null = auto-detect mysqldump / mariadb-dump
    'dump_timeout' => (int) env('BACKUP_DUMP_TIMEOUT', 3600),
    'gzip_level' => (int) env('BACKUP_GZIP_LEVEL', 6),

    /*
    |--------------------------------------------------------------------------
    | PHP fallback (legacy) — only when mysqldump is unavailable
    |--------------------------------------------------------------------------
    */
    'php_fallback_enabled' => (bool) env('BACKUP_PHP_FALLBACK', true),
    'php_fallback_max_bytes' => (int) env('BACKUP_PHP_FALLBACK_MAX_BYTES', 256 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Upload / multipart (S3-compatible including IDrive e2)
    |--------------------------------------------------------------------------
    */
    'multipart_threshold' => (int) env('BACKUP_MULTIPART_THRESHOLD', 16 * 1024 * 1024),
    'multipart_part_size' => (int) env('BACKUP_MULTIPART_PART_SIZE', 16 * 1024 * 1024),
    'store_from_path_max_fallback_bytes' => (int) env('BACKUP_STORE_FALLBACK_MAX', 64 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Jobs / queue
    |--------------------------------------------------------------------------
    | CreateBackupJob timeout is 3600s. Configure queue workers with a higher
    | --timeout and set retry_after (e.g. in config/queue.php) above the job
    | timeout so the job is not released as "retry" while still running.
    */
    'job_timeout' => (int) env('BACKUP_JOB_TIMEOUT', 3600),
    'always_queue' => (bool) env('BACKUP_ALWAYS_QUEUE', false),
    'schedule_dispatch_sync' => (bool) env('BACKUP_SCHEDULE_DISPATCH_SYNC', false),
    'stuck_running_minutes' => (int) env('BACKUP_STUCK_RUNNING_MINUTES', 120),

];
