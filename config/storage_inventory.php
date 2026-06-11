<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File sources referenced in the database
    |--------------------------------------------------------------------------
    */
    'sources' => [
        [
            'key' => 'blog_posts',
            'label' => 'المدونة',
            'model' => \App\Models\BlogPost::class,
            'column' => 'featured_image',
            'disk' => 'blog_images',
            'path_prefix' => 'blog/images/',
            'route_name' => 'admin.blog.posts.show',
            'route_param' => 'post',
        ],
        [
            'key' => 'courses',
            'label' => 'الكورسات',
            'model' => \App\Models\Course::class,
            'column' => 'image',
            'disk' => 'public',
            'path_prefix' => 'courses/images/',
            'route_name' => 'courses.show',
            'route_param' => 'course',
        ],
        [
            'key' => 'frontend_courses',
            'label' => 'كورسات الواجهة',
            'model' => \App\Models\FrontendCourse::class,
            'column' => 'thumbnail',
            'disk' => 'course_thumbnails',
            'path_prefix' => 'courses/thumbnails/',
            'route_name' => 'admin.frontend-courses.show',
            'route_param' => 'frontend_course',
        ],
        [
            'key' => 'student_gifts',
            'label' => 'هدايا الطلاب',
            'model' => \App\Models\StudentGift::class,
            'column' => 'image_path',
            'disk' => 'gift_images',
            'path_prefix' => 'gifts/images/',
            'route_name' => 'admin.gifts.show',
            'route_param' => 'gift',
        ],
        [
            'key' => 'users',
            'label' => 'صور المستخدمين',
            'model' => \App\Models\User::class,
            'column' => 'photo',
            'disk' => 'public',
            'path_prefix' => 'users/photos/',
            'route_name' => null,
            'route_param' => null,
            'path_filter' => 'users/photos/',
        ],
        [
            'key' => 'profile_photos',
            'label' => 'صور الملف الشخصي',
            'model' => \App\Models\User::class,
            'column' => 'photo',
            'disk' => 'public',
            'path_prefix' => 'profile-photos/',
            'route_name' => null,
            'route_param' => null,
            'path_filter' => 'profile-photos/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recommended migration order (phases)
    |--------------------------------------------------------------------------
    */
    'phases' => [
        'blog' => ['blog_posts'],
        'courses' => ['courses', 'frontend_courses'],
        'gifts' => ['student_gifts'],
        'profiles' => ['users', 'profile_photos'],
    ],

    'cloud_drivers' => [
        's3',
        'digitalocean',
        'wasabi',
        'backblaze',
        'cloudflare_r2',
        'bunny',
        'google_drive',
        'dropbox',
        'azure',
        'ftp',
        'sftp',
    ],

    'migration_batch_size' => 50,

    'inventory_cache_key' => 'storage_inventory_scan_results',

    'inventory_cache_ttl' => 600,

    'migration_progress_cache_key' => 'storage_migration_progress',

];
