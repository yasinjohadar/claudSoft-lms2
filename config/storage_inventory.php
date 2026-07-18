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
            'label' => 'هدايا الطلاب — صور',
            'model' => \App\Models\StudentGift::class,
            'column' => 'image_path',
            'disk' => 'gift_images',
            'path_prefix' => 'gifts/images/',
            'route_name' => 'admin.gifts.show',
            'route_param' => 'gift',
        ],
        [
            'key' => 'student_gifts_previews',
            'label' => 'هدايا الطلاب — معاينة',
            'model' => \App\Models\StudentGift::class,
            'column' => 'preview_file_path',
            'disk' => 'public',
            'path_prefix' => 'gifts/previews/',
            'route_name' => 'admin.gifts.show',
            'route_param' => 'gift',
        ],
        [
            'key' => 'student_gifts_downloads',
            'label' => 'هدايا الطلاب — تنزيل',
            'model' => \App\Models\StudentGift::class,
            'column' => 'download_file_path',
            'disk' => 'public',
            'path_prefix' => 'gifts/downloads/',
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
        [
            'key' => 'payments',
            'label' => 'إيصالات الدفع',
            'model' => \App\Models\Payment::class,
            'column' => 'receipt_path',
            'disk' => 'payment_receipts',
            'disk_column' => 'receipt_disk',
            'path_prefix' => 'payments/receipts/',
            'route_name' => null,
            'route_param' => null,
        ],
        [
            'key' => 'group_registrations',
            'label' => 'وصول انتساب المجموعات',
            'model' => \App\Models\GroupRegistration::class,
            'column' => 'membership_receipt_path',
            'disk' => 'payment_receipts',
            'disk_column' => 'membership_receipt_disk',
            'path_prefix' => 'group-registrations/payment-receipts/',
            'route_name' => null,
            'route_param' => null,
        ],
        [
            'key' => 'resources',
            'label' => 'الموارد التعليمية',
            'model' => \App\Models\Resource::class,
            'column' => 'file_path',
            'disk' => 'public',
            'path_prefix' => 'resources/',
            'route_name' => null,
            'route_param' => null,
        ],
        [
            'key' => 'videos',
            'label' => 'الفيديوهات',
            'model' => \App\Models\Video::class,
            'column' => 'video_path',
            'disk' => 'public',
            'path_prefix' => 'videos/',
            'route_name' => null,
            'route_param' => null,
        ],
        [
            'key' => 'video_thumbnails',
            'label' => 'صور مصغرة للفيديو',
            'model' => \App\Models\Video::class,
            'column' => 'thumbnail',
            'disk' => 'public',
            'path_prefix' => 'videos/',
            'route_name' => null,
            'route_param' => null,
        ],
        [
            'key' => 'certificates_pdf',
            'label' => 'شهادات PDF',
            'model' => \App\Models\Certificate::class,
            'column' => 'pdf_path',
            'disk' => 'public',
            'path_prefix' => 'certificates/',
            'route_name' => null,
            'route_param' => null,
        ],
        [
            'key' => 'certificates_qr',
            'label' => 'QR الشهادات',
            'model' => \App\Models\Certificate::class,
            'column' => 'qr_code_path',
            'disk' => 'public',
            'path_prefix' => 'certificates/',
            'route_name' => null,
            'route_param' => null,
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
        'gifts' => ['student_gifts', 'student_gifts_previews', 'student_gifts_downloads'],
        'profiles' => ['users', 'profile_photos'],
        'receipts' => ['payments', 'group_registrations'],
        'resources' => ['resources'],
        'videos' => ['videos', 'video_thumbnails'],
        'certificates' => ['certificates_pdf', 'certificates_qr'],
    ],

    'phase_labels' => [
        'blog' => 'المدونة',
        'courses' => 'الكورسات',
        'gifts' => 'الهدايا',
        'profiles' => 'الملفات الشخصية',
        'receipts' => 'الإيصالات',
        'resources' => 'الموارد',
        'videos' => 'الفيديو',
        'certificates' => 'الشهادات',
    ],

    'status_labels' => [
        'cloud_only' => 'سحابة فقط',
        'local_only' => 'محلي فقط',
        'both' => 'نسختان (محلي + سحابة)',
        'missing' => 'مفقود',
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
