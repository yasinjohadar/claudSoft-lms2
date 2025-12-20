<?php
/**
 * ملف اختبار الصور - احذفه بعد حل المشكلة!
 * 
 * ضع هذا الملف في مجلد public وافتحه في المتصفح:
 * https://yourdomain.com/test-images.php
 * 
 * ⚠️ احذف هذا الملف بعد الانتهاء!
 */

echo "<h1>اختبار الصور على السيرفر</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; direction: rtl; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    .test-image { max-width: 200px; margin: 10px; border: 2px solid #ddd; padding: 5px; }
</style>";

// 1. اختبار Symbolic Link
echo "<h2>1. اختبار Symbolic Link:</h2>";
$storage_link = __DIR__ . '/storage';
$storage_target = __DIR__ . '/../storage/app/public';

if (file_exists($storage_link)) {
    if (is_link($storage_link)) {
        $link_target = readlink($storage_link);
        echo "<div class='success'>✅ Symbolic Link موجود</div>";
        echo "<div class='info'>الرابط يشير إلى: $link_target</div>";
        
        if (file_exists($link_target)) {
            echo "<div class='success'>✅ المسار المستهدف موجود</div>";
        } else {
            echo "<div class='error'>❌ المسار المستهدف غير موجود: $link_target</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ يوجد مجلد storage لكنه ليس symbolic link</div>";
    }
} else {
    echo "<div class='error'>❌ Symbolic Link غير موجود</div>";
    echo "<div class='info'>الحل: قم بتشغيل <code>php artisan storage:link</code></div>";
}

// 2. اختبار مجلد الصور
echo "<h2>2. اختبار مجلد الصور:</h2>";
$thumbnails_path = __DIR__ . '/../storage/app/public/courses/thumbnails';

if (file_exists($thumbnails_path)) {
    echo "<div class='success'>✅ مجلد الصور موجود: $thumbnails_path</div>";
    
    // عرض الملفات
    $files = glob($thumbnails_path . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    if (count($files) > 0) {
        echo "<div class='success'>✅ عدد ملفات الصور: " . count($files) . "</div>";
        echo "<div class='info'>أول 5 ملفات:</div>";
        echo "<pre>";
        foreach (array_slice($files, 0, 5) as $file) {
            echo basename($file) . " (" . number_format(filesize($file) / 1024, 2) . " KB)\n";
        }
        echo "</pre>";
    } else {
        echo "<div class='warning'>⚠️ المجلد موجود لكنه فارغ (لا توجد صور)</div>";
    }
    
    // اختبار الصلاحيات
    if (is_readable($thumbnails_path)) {
        echo "<div class='success'>✅ المجلد قابل للقراءة</div>";
    } else {
        echo "<div class='error'>❌ المجلد غير قابل للقراءة - صلاحيات: " . substr(sprintf('%o', fileperms($thumbnails_path)), -4) . "</div>";
    }
} else {
    echo "<div class='error'>❌ مجلد الصور غير موجود: $thumbnails_path</div>";
}

// 3. اختبار APP_URL
echo "<h2>3. اختبار APP_URL:</h2>";
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_content = file_get_contents($env_file);
    if (preg_match('/APP_URL=(.+)/', $env_content, $matches)) {
        $app_url = trim($matches[1]);
        echo "<div class='info'>APP_URL: $app_url</div>";
        
        if (strpos($app_url, 'http') === 0) {
            echo "<div class='success'>✅ APP_URL صحيح</div>";
        } else {
            echo "<div class='warning'>⚠️ APP_URL يجب أن يبدأ بـ http:// أو https://</div>";
        }
    } else {
        echo "<div class='error'>❌ APP_URL غير موجود في .env</div>";
    }
} else {
    echo "<div class='error'>❌ ملف .env غير موجود</div>";
}

// 4. اختبار Storage URL
echo "<h2>4. اختبار Storage URL:</h2>";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // اختبار ملف Storage
    $test_file = 'courses/thumbnails/test.jpg';
    $storage_url = \Storage::disk('public')->url($test_file);
    echo "<div class='info'>مثال على Storage URL: $storage_url</div>";
    
    // التحقق من config
    $storage_disk = config('filesystems.disks.public');
    echo "<div class='info'>Storage Root: " . $storage_disk['root'] . "</div>";
    echo "<div class='info'>Storage URL: " . $storage_disk['url'] . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ خطأ في تحميل Laravel: " . $e->getMessage() . "</div>";
}

// 5. اختبار الصور الفعلية من قاعدة البيانات
echo "<h2>5. اختبار الصور من قاعدة البيانات:</h2>";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // محاولة الاتصال بقاعدة البيانات
    $courses = \App\Models\FrontendCourse::whereNotNull('thumbnail')
                                         ->where('thumbnail', '!=', '')
                                         ->limit(5)
                                         ->get();
    
    if ($courses->count() > 0) {
        echo "<div class='success'>✅ تم العثور على " . $courses->count() . " كورس مع صور</div>";
        
        foreach ($courses as $course) {
            echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
            echo "<h3>الكورس: " . $course->title . "</h3>";
            echo "<p><strong>Thumbnail Path:</strong> " . $course->thumbnail . "</p>";
            
            // اختبار URL
            $thumbnail_url = $course->thumbnail_url;
            echo "<p><strong>Thumbnail URL:</strong> <a href='$thumbnail_url' target='_blank'>$thumbnail_url</a></p>";
            
            // التحقق من وجود الملف
            $file_path = storage_path('app/public/' . $course->thumbnail);
            if (file_exists($file_path)) {
                echo "<div class='success'>✅ الملف موجود في: $file_path</div>";
                echo "<div class='info'>حجم الملف: " . number_format(filesize($file_path) / 1024, 2) . " KB</div>";
                
                // محاولة عرض الصورة
                echo "<div style='margin-top: 10px;'>";
                echo "<img src='$thumbnail_url' alt='{$course->title}' class='test-image' onerror=\"this.style.border='3px solid red'; this.alt='فشل تحميل الصورة';\">";
                echo "</div>";
            } else {
                echo "<div class='error'>❌ الملف غير موجود في: $file_path</div>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ لا توجد كورسات مع صور في قاعدة البيانات</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "</div>";
}

// 6. اختبار الوصول المباشر
echo "<h2>6. اختبار الوصول المباشر:</h2>";
if (file_exists($storage_link) && is_link($storage_link)) {
    $test_files = glob($thumbnails_path . '/*.{jpg,jpeg,png}', GLOB_BRACE);
    if (count($test_files) > 0) {
        $test_file = basename($test_files[0]);
        $direct_url = url('storage/courses/thumbnails/' . $test_file);
        echo "<div class='info'>رابط مباشر للاختبار: <a href='$direct_url' target='_blank'>$direct_url</a></div>";
        echo "<div style='margin-top: 10px;'>";
        echo "<img src='$direct_url' alt='Test Image' class='test-image' onerror=\"this.style.border='3px solid red'; this.alt='فشل تحميل الصورة - تحقق من Symbolic Link';\">";
        echo "</div>";
    }
}

// 7. التوصيات
echo "<hr>";
echo "<h2>📋 التوصيات:</h2>";
echo "<ol>";
if (!file_exists($storage_link) || !is_link($storage_link)) {
    echo "<li><strong>قم بإنشاء Symbolic Link:</strong><br>";
    echo "<code>php artisan storage:link</code><br>";
    echo "أو يدوياً:<br>";
    echo "<code>ln -s " . realpath($storage_target) . " " . $storage_link . "</code></li>";
}

if (file_exists($thumbnails_path) && !is_readable($thumbnails_path)) {
    echo "<li><strong>إصلاح الصلاحيات:</strong><br>";
    echo "<code>chmod -R 755 storage/app/public</code></li>";
}

echo "<li><strong>مسح الـ Cache:</strong><br>";
echo "<code>php artisan config:clear<br>";
echo "php artisan cache:clear<br>";
echo "php artisan view:clear</code></li>";

echo "<li><strong>تأكد من رفع الصور:</strong><br>";
echo "تأكد من رفع جميع الملفات من <code>storage/app/public/courses/thumbnails/</code> إلى السيرفر</li>";

echo "</ol>";

echo "<hr>";
echo "<p><strong>⚠️ مهم:</strong> احذف هذا الملف بعد حل المشكلة!</p>";


