<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local Dev Quick Login Path
    |--------------------------------------------------------------------------
    |
    | Obscure URL segment for the local-only quick login page.
    | Example full URL: http://127.0.0.1:8000/_dev/platform-access
    |
    */

    'path' => env('LOCAL_DEV_LOGIN_PATH', '_dev/platform-access'),

    'admin_email' => env('LOCAL_DEV_ADMIN_EMAIL', 'admin@admin.com'),

    'student_email' => env('LOCAL_DEV_STUDENT_EMAIL', 'student@gmail.com'),

];
