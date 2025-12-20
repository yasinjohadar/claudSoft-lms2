<?php
/**
 * ملف اختبار خطأ 500
 * 
 * ⚠️ مهم: احذف هذا الملف بعد حل المشكلة!
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار خطأ 500</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-right: 4px solid #3498db;
        }
        .success { border-right-color: #27ae60; }
        .error { border-right-color: #e74c3c; }
        .warning { border-right-color: #f39c12; }
        h1 { color: #2c3e50; }
        h2 { color: #34495e; margin-top: 30px; }
        code { background: #ecf0f1; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 اختبار خطأ HTTP 500</h1>
    <p><strong>⚠️ مهم:</strong> احذف هذا الملف بعد حل المشكلة!</p>

    <h2>1. معلومات PHP</h2>
    <div class="test-item <?php echo version_compare(PHP_VERSION, '8.2.0', '>=') ? 'success' : 'error'; ?>">
        <strong>إصدار PHP:</strong> <?php echo PHP_VERSION; ?>
        <?php if (version_compare(PHP_VERSION, '8.2.0', '>=')): ?>
            ✅ مناسب لـ Laravel 11
        <?php else: ?>
            ❌ يحتاج PHP 8.2 أو أحدث
        <?php endif; ?>
    </div>

    <h2>2. الإضافات المطلوبة</h2>
    <?php
    $required = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'];
    foreach ($required as $ext):
        $loaded = extension_loaded($ext);
    ?>
    <div class="test-item <?php echo $loaded ? 'success' : 'error'; ?>">
        <strong><?php echo $ext; ?>:</strong>
        <?php echo $loaded ? '✅ مفعّل' : '❌ غير مفعّل'; ?>
    </div>
    <?php endforeach; ?>

    <h2>3. ملفات Laravel</h2>
    <?php
    // تحديد المسار الصحيح للمشروع
    $basePath = dirname(__DIR__); // يرجع من public إلى الجذر
    $publicPath = __DIR__; // مسار public
    
    // محاولة عدة مسارات محتملة
    $possiblePaths = [
        $basePath, // ../ من public
        dirname($basePath), // ../../ إذا كان public داخل مجلد آخر
        $_SERVER['DOCUMENT_ROOT'], // Document Root
        realpath($basePath), // المسار الحقيقي
    ];
    
    $files = [
        '.env' => 'ملف .env',
        'artisan' => 'ملف artisan',
        'composer.json' => 'ملف composer.json',
        'bootstrap/app.php' => 'ملف bootstrap/app.php',
    ];
    
    $foundBase = null;
    foreach ($possiblePaths as $path) {
        if ($path && file_exists($path . '/artisan')) {
            $foundBase = $path;
            break;
        }
    }
    
    if (!$foundBase) {
        // محاولة البحث في المسارات الشائعة
        $commonPaths = [
            '/home/' . get_current_user() . '/public_html',
            '/home/' . get_current_user() . '/domains/claudsoft.com/public_html',
            '/var/www/html',
            $_SERVER['DOCUMENT_ROOT'],
        ];
        
        foreach ($commonPaths as $path) {
            if ($path && file_exists($path . '/artisan')) {
                $foundBase = $path;
                break;
            }
        }
    }
    
    if ($foundBase):
        echo '<div class="test-item success"><strong>✅ تم العثور على المشروع في:</strong> ' . htmlspecialchars($foundBase) . '</div>';
        
        foreach ($files as $file => $name):
            $fullPath = $foundBase . '/' . $file;
            $exists = file_exists($fullPath);
    ?>
    <div class="test-item <?php echo $exists ? 'success' : 'error'; ?>">
        <strong><?php echo $name; ?>:</strong>
        <?php if ($exists): ?>
            ✅ موجود في: <code><?php echo htmlspecialchars($fullPath); ?></code>
        <?php else: ?>
            ❌ غير موجود في: <code><?php echo htmlspecialchars($fullPath); ?></code>
        <?php endif; ?>
    </div>
    <?php 
        endforeach;
        
        // فحص public/index.php
        $indexPath = $foundBase . '/public/index.php';
        $indexExists = file_exists($indexPath);
    ?>
    <div class="test-item <?php echo $indexExists ? 'success' : 'error'; ?>">
        <strong>ملف public/index.php:</strong>
        <?php if ($indexExists): ?>
            ✅ موجود في: <code><?php echo htmlspecialchars($indexPath); ?></code>
        <?php else: ?>
            ❌ غير موجود في: <code><?php echo htmlspecialchars($indexPath); ?></code>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="test-item error">
        <strong>⚠️ لم يتم العثور على المشروع تلقائياً</strong>
        <br><small>المسارات المحتملة:</small>
        <ul>
            <?php foreach ($possiblePaths as $path): ?>
                <li><code><?php echo htmlspecialchars($path ?: 'NULL'); ?></code></li>
            <?php endforeach; ?>
        </ul>
        <br><strong>💡 الحل:</strong> تحقق من Document Root في cPanel
    </div>
    <?php endif; ?>

    <h2>4. المجلدات والصلاحيات</h2>
    <?php
    if ($foundBase):
        $dirs = [
            $foundBase . '/storage' => 'مجلد storage',
            $foundBase . '/bootstrap/cache' => 'مجلد bootstrap/cache',
            $foundBase . '/vendor' => 'مجلد vendor',
        ];
        foreach ($dirs as $dir => $name):
            $exists = is_dir($dir);
            $writable = $exists && is_writable($dir);
    ?>
    <div class="test-item <?php echo $exists && $writable ? 'success' : ($exists ? 'warning' : 'error'); ?>">
        <strong><?php echo basename($dir); ?>:</strong>
        <?php if ($exists && $writable): ?>
            ✅ موجود ويمكن الكتابة
        <?php elseif ($exists): ?>
            ⚠️ موجود لكن غير قابل للكتابة (تحقق من الصلاحيات)
        <?php else: ?>
            ❌ غير موجود في: <code><?php echo htmlspecialchars($dir); ?></code>
        <?php endif; ?>
    </div>
    <?php 
        endforeach;
    else:
    ?>
    <div class="test-item warning">
        ⚠️ لا يمكن فحص المجلدات (لم يتم العثور على المشروع)
    </div>
    <?php endif; ?>

    <h2>5. ملف .env</h2>
    <?php
    $envPath = $foundBase ? $foundBase . '/.env' : '../.env';
    if ($foundBase && file_exists($envPath)):
        $envContent = file_get_contents($envPath);
        $hasAppKey = strpos($envContent, 'APP_KEY=') !== false && strpos($envContent, 'APP_KEY=') !== strpos($envContent, 'APP_KEY=');
        $hasDb = strpos($envContent, 'DB_DATABASE=') !== false;
    ?>
    <div class="test-item <?php echo $hasAppKey ? 'success' : 'error'; ?>">
        <strong>APP_KEY:</strong>
        <?php echo $hasAppKey ? '✅ موجود' : '❌ غير موجود (قم بتشغيل: php artisan key:generate)'; ?>
    </div>
    <div class="test-item <?php echo $hasDb ? 'success' : 'warning'; ?>">
        <strong>إعدادات قاعدة البيانات:</strong>
        <?php echo $hasDb ? '✅ موجودة' : '⚠️ غير موجودة'; ?>
    </div>
    <?php else: ?>
    <div class="test-item error">
        <strong>ملف .env:</strong> ❌ غير موجود
    </div>
    <?php endif; ?>

    <h2>6. mod_rewrite</h2>
    <div class="test-item <?php echo function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? 'success' : 'warning'; ?>">
        <strong>mod_rewrite:</strong>
        <?php if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())): ?>
            ✅ مفعّل
        <?php else: ?>
            ⚠️ لا يمكن التحقق (قد يكون مفعّل في Apache)
        <?php endif; ?>
    </div>

    <h2>7. اختبار قاعدة البيانات</h2>
    <?php
    if ($foundBase && file_exists($envPath)):
        $envContent = file_get_contents($envPath);
        preg_match('/DB_HOST=(.+)/', $envContent, $host);
        preg_match('/DB_DATABASE=(.+)/', $envContent, $db);
        preg_match('/DB_USERNAME=(.+)/', $envContent, $user);
        preg_match('/DB_PASSWORD=(.+)/', $envContent, $pass);
        
        if (!empty($host[1]) && !empty($db[1]) && !empty($user[1])):
            $host = trim($host[1]);
            $db = trim($db[1]);
            $user = trim($user[1]);
            $pass = !empty($pass[1]) ? trim($pass[1]) : '';
            
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    ?>
    <div class="test-item success">
        <strong>الاتصال بقاعدة البيانات:</strong> ✅ نجح
    </div>
    <?php
            } catch (PDOException $e) {
    ?>
    <div class="test-item error">
        <strong>الاتصال بقاعدة البيانات:</strong> ❌ فشل
        <br><small>الخطأ: <?php echo htmlspecialchars($e->getMessage()); ?></small>
    </div>
    <?php
            }
        else:
    ?>
    <div class="test-item warning">
        <strong>الاتصال بقاعدة البيانات:</strong> ⚠️ لا يمكن الاختبار (إعدادات غير مكتملة)
    </div>
    <?php
        endif;
    else:
    ?>
    <div class="test-item warning">
        <strong>الاتصال بقاعدة البيانات:</strong> ⚠️ لا يمكن الاختبار (ملف .env غير موجود)
    </div>
    <?php endif; ?>

    <h2>8. Symbolic Link للـ Storage</h2>
    <?php
    if ($foundBase):
        $storageLink = $foundBase . '/public/storage';
        $linkExists = is_link($storageLink) || (is_dir($storageLink) && file_exists($storageLink . '/.gitignore'));
    ?>
    <div class="test-item <?php echo $linkExists ? 'success' : 'warning'; ?>">
        <strong>Storage Link:</strong>
        <?php if ($linkExists): ?>
            ✅ موجود في: <code><?php echo htmlspecialchars($storageLink); ?></code>
        <?php else: ?>
            ⚠️ غير موجود في: <code><?php echo htmlspecialchars($storageLink); ?></code>
            <br><small>قم بتشغيل: <code>php artisan storage:link</code></small>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="test-item warning">
        ⚠️ لا يمكن فحص Storage Link (لم يتم العثور على المشروع)
    </div>
    <?php endif; ?>
    
    <h2>9. معلومات إضافية</h2>
    <div class="test-item">
        <strong>Document Root:</strong> <code><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'غير محدد'); ?></code>
    </div>
    <div class="test-item">
        <strong>Script Path:</strong> <code><?php echo htmlspecialchars(__FILE__); ?></code>
    </div>
    <div class="test-item">
        <strong>Current Directory:</strong> <code><?php echo htmlspecialchars(getcwd()); ?></code>
    </div>

    <hr style="margin: 30px 0;">
    <p><strong>⚠️ مهم جداً:</strong> احذف هذا الملف (<code>test-500.php</code>) بعد حل المشكلة!</p>
    <p><strong>💡 نصيحة:</strong> إذا رأيت أي ❌، ابدأ بحلها بالترتيب من الأعلى إلى الأسفل.</p>
</body>
</html>
