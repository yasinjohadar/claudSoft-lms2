<?php
/**
 * ملف اختبار سريع - احذفه بعد حل المشكلة!
 * 
 * ضع هذا الملف في مجلد public وافتحه في المتصفح:
 * https://yourdomain.com/test.php
 */

echo "<h1>اختبار إعدادات السيرفر</h1>";

// 1. اختبار PHP
echo "<h2>1. إصدار PHP:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "المطلوب: PHP 8.2+<br>";
if (version_compare(phpversion(), '8.2.0', '>=')) {
    echo "<span style='color: green;'>✅ إصدار PHP مناسب</span><br>";
} else {
    echo "<span style='color: red;'>❌ إصدار PHP قديم - يجب الترقية إلى 8.2+</span><br>";
}

// 2. اختبار الإضافات المطلوبة
echo "<h2>2. الإضافات المطلوبة:</h2>";
$required_extensions = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color: green;'>✅ $ext</span><br>";
    } else {
        echo "<span style='color: red;'>❌ $ext غير مثبت</span><br>";
    }
}

// 3. اختبار الصلاحيات
echo "<h2>3. الصلاحيات:</h2>";
$paths_to_check = [
    '../storage' => 'storage',
    '../storage/framework' => 'storage/framework',
    '../storage/logs' => 'storage/logs',
    '../bootstrap/cache' => 'bootstrap/cache',
];

foreach ($paths_to_check as $path => $name) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        if (is_writable($path)) {
            echo "<span style='color: green;'>✅ $name قابل للكتابة (صلاحيات: $perms)</span><br>";
        } else {
            echo "<span style='color: red;'>❌ $name غير قابل للكتابة (صلاحيات: $perms) - يجب أن يكون 755 أو 775</span><br>";
        }
    } else {
        echo "<span style='color: red;'>❌ $name غير موجود</span><br>";
    }
}

// 4. اختبار ملفات Laravel الأساسية
echo "<h2>4. ملفات Laravel الأساسية:</h2>";
$required_files = [
    '../vendor/autoload.php' => 'vendor/autoload.php',
    '../bootstrap/app.php' => 'bootstrap/app.php',
    '../.env' => '.env',
    '../artisan' => 'artisan',
];

foreach ($required_files as $file => $name) {
    if (file_exists($file)) {
        echo "<span style='color: green;'>✅ $name موجود</span><br>";
    } else {
        echo "<span style='color: red;'>❌ $name غير موجود</span><br>";
    }
}

// 5. اختبار قاعدة البيانات (إذا كان .env موجود)
echo "<h2>5. اختبار قاعدة البيانات:</h2>";
if (file_exists('../.env')) {
    $env_content = file_get_contents('../.env');
    if (strpos($env_content, 'DB_CONNECTION') !== false) {
        echo "<span style='color: green;'>✅ ملف .env يحتوي على إعدادات قاعدة البيانات</span><br>";
        
        // محاولة قراءة إعدادات قاعدة البيانات
        preg_match('/DB_HOST=(.+)/', $env_content, $host_match);
        preg_match('/DB_DATABASE=(.+)/', $env_content, $db_match);
        preg_match('/DB_USERNAME=(.+)/', $env_content, $user_match);
        
        if (!empty($host_match) && !empty($db_match) && !empty($user_match)) {
            echo "Host: " . trim($host_match[1]) . "<br>";
            echo "Database: " . trim($db_match[1]) . "<br>";
            echo "Username: " . trim($user_match[1]) . "<br>";
        }
    } else {
        echo "<span style='color: red;'>❌ ملف .env لا يحتوي على إعدادات قاعدة البيانات</span><br>";
    }
    
    // التحقق من APP_KEY
    if (strpos($env_content, 'APP_KEY=base64:') !== false || strpos($env_content, 'APP_KEY=') !== false) {
        if (strpos($env_content, 'APP_KEY=base64:') !== false && strlen($env_content) > 20) {
            echo "<span style='color: green;'>✅ APP_KEY موجود</span><br>";
        } else {
            echo "<span style='color: orange;'>⚠️ APP_KEY موجود لكن قد يكون فارغاً - قم بتشغيل: php artisan key:generate</span><br>";
        }
    } else {
        echo "<span style='color: red;'>❌ APP_KEY غير موجود - قم بتشغيل: php artisan key:generate</span><br>";
    }
} else {
    echo "<span style='color: red;'>❌ ملف .env غير موجود</span><br>";
}

// 6. اختبار mod_rewrite
echo "<h2>6. اختبار mod_rewrite:</h2>";
if (function_exists('apache_get_modules')) {
    if (in_array('mod_rewrite', apache_get_modules())) {
        echo "<span style='color: green;'>✅ mod_rewrite مفعّل</span><br>";
    } else {
        echo "<span style='color: red;'>❌ mod_rewrite غير مفعّل - يجب تفعيله في cPanel</span><br>";
    }
} else {
    echo "<span style='color: orange;'>⚠️ لا يمكن التحقق من mod_rewrite (قد يكون Nginx بدلاً من Apache)</span><br>";
}

// 7. معلومات إضافية
echo "<h2>7. معلومات إضافية:</h2>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'غير معروف') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'غير معروف') . "<br>";
echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'غير معروف') . "<br>";

echo "<hr>";
echo "<p><strong>⚠️ مهم:</strong> احذف هذا الملف بعد حل المشكلة!</p>";
echo "<p>إذا كانت جميع الاختبارات ✅ خضراء، المشكلة قد تكون في:</p>";
echo "<ul>";
echo "<li>إعدادات قاعدة البيانات</li>";
echo "<li>مشكلة في الكود نفسه</li>";
echo "<li>مشكلة في الـ routes</li>";
echo "<li>تحقق من <code>storage/logs/laravel.log</code></li>";
echo "</ul>";



