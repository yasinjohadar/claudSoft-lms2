<?php

return [
    'piston' => [
        'url' => env('PISTON_URL', 'http://piston:2000'),
        'timeout' => (int) env('PISTON_TIMEOUT', 10),
        'version' => env('PISTON_VERSION', '*'),
        'max_code_size' => (int) env('CHALLENGE_MAX_CODE_SIZE', 51200),
    ],

    'auto_save_interval' => 30,

    'rate_limit' => [
        'runs_per_hour' => (int) env('CHALLENGE_RUNS_PER_HOUR', 30),
    ],
];
