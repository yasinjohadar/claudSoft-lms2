<?php
/**
 * ملف اختبار قاعدة البيانات - احذفه بعد حل المشكلة!
 * 
 * ضع هذا الملف في مجلد public وافتحه في المتصفح:
 * https://yourdomain.com/test-db.php
 * 
 * ⚠️ احذف هذا الملف بعد الانتهاء!
 */

echo "<h1>اختبار اتصال قاعدة البيانات</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// قراءة إعدادات .env
$env_file = __DIR__ . '/../.env';

if (!file_exists($env_file)) {
    echo "<div class='error'>❌ ملف .env غير موجود في: $env_file</div>";
    exit;
}

echo "<div class='info'>✅ ملف .env موجود</div>";

// قراءة محتوى .env
$env_content = file_get_contents($env_file);

// استخراج إعدادات قاعدة البيانات
$db_config = [];
$patterns = [
    'DB_CONNECTION' => '/DB_CONNECTION=(.+)/',
    'DB_HOST' => '/DB_HOST=(.+)/',
    'DB_PORT' => '/DB_PORT=(.+)/',
    'DB_DATABASE' => '/DB_DATABASE=(.+)/',
    'DB_USERNAME' => '/DB_USERNAME=(.+)/',
    'DB_PASSWORD' => '/DB_PASSWORD=(.+)/',
];

foreach ($patterns as $key => $pattern) {
    if (preg_match($pattern, $env_content, $matches)) {
        $db_config[$key] = trim($matches[1]);
    } else {
        $db_config[$key] = null;
    }
}

// عرض الإعدادات (بدون كلمة المرور)
echo "<h2>إعدادات قاعدة البيانات من .env:</h2>";
echo "<pre>";
echo "DB_CONNECTION: " . ($db_config['DB_CONNECTION'] ?? 'غير موجود') . "\n";
echo "DB_HOST: " . ($db_config['DB_HOST'] ?? 'غير موجود') . "\n";
echo "DB_PORT: " . ($db_config['DB_PORT'] ?? '3306') . "\n";
echo "DB_DATABASE: " . ($db_config['DB_DATABASE'] ?? 'غير موجود') . "\n";
echo "DB_USERNAME: " . ($db_config['DB_USERNAME'] ?? 'غير موجود') . "\n";
echo "DB_PASSWORD: " . (isset($db_config['DB_PASSWORD']) ? '***' . substr($db_config['DB_PASSWORD'], -2) : 'غير موجود');
echo "</pre>";

// التحقق من وجود جميع الإعدادات
$missing = [];
foreach (['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
    if (empty($db_config[$key])) {
        $missing[] = $key;
    }
}

if (!empty($missing)) {
    echo "<div class='error'>❌ الإعدادات التالية مفقودة: " . implode(', ', $missing) . "</div>";
    exit;
}

// اختبار الاتصال
echo "<h2>اختبار الاتصال:</h2>";

$host = $db_config['DB_HOST'];
$port = $db_config['DB_PORT'] ?? '3306';
$database = $db_config['DB_DATABASE'];
$username = $db_config['DB_USERNAME'];
$password = $db_config['DB_PASSWORD'];

// اختبار 1: الاتصال بدون قاعدة البيانات
echo "<h3>1. اختبار الاتصال بـ MySQL (بدون قاعدة البيانات):</h3>";
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ الاتصال بـ MySQL نجح!</div>";
    
    // عرض معلومات MySQL
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "<div class='info'>إصدار MySQL: $version</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ فشل الاتصال بـ MySQL</div>";
    echo "<pre>الخطأ: " . $e->getMessage() . "</pre>";
    
    // اقتراحات
    echo "<h3>💡 اقتراحات:</h3>";
    echo "<ul>";
    if ($host === 'localhost') {
        echo "<li><strong>جرب تغيير DB_HOST من 'localhost' إلى '127.0.0.1' في ملف .env</strong></li>";
    }
    echo "<li>تحقق من اسم المستخدم في cPanel → MySQL Databases</li>";
    echo "<li>تحقق من كلمة المرور (استخدم Copy/Paste مباشرة)</li>";
    echo "<li>تأكد من أن المستخدم موجود في MySQL</li>";
    echo "</ul>";
    exit;
}

// اختبار 2: التحقق من وجود قاعدة البيانات
echo "<h3>2. التحقق من وجود قاعدة البيانات:</h3>";
try {
    $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array($database, $databases)) {
        echo "<div class='success'>✅ قاعدة البيانات '$database' موجودة</div>";
    } else {
        echo "<div class='error'>❌ قاعدة البيانات '$database' غير موجودة</div>";
        echo "<div class='info'>قواعد البيانات المتاحة:</div>";
        echo "<pre>" . implode("\n", $databases) . "</pre>";
        exit;
    }
} catch(PDOException $e) {
    echo "<div class='error'>❌ خطأ في التحقق من قواعد البيانات: " . $e->getMessage() . "</div>";
    exit;
}

// اختبار 3: الاتصال بقاعدة البيانات
echo "<h3>3. اختبار الاتصال بقاعدة البيانات:</h3>";
try {
    $pdo_db = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ الاتصال بقاعدة البيانات نجح!</div>";
    
    // اختبار 4: التحقق من الصلاحيات
    echo "<h3>4. التحقق من الصلاحيات:</h3>";
    try {
        $grants = $pdo_db->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll(PDO::FETCH_COLUMN);
        echo "<div class='success'>✅ الصلاحيات:</div>";
        echo "<pre>" . implode("\n", $grants) . "</pre>";
    } catch(PDOException $e) {
        echo "<div class='error'>⚠️ لا يمكن التحقق من الصلاحيات: " . $e->getMessage() . "</div>";
    }
    
    // اختبار 5: اختبار استعلام بسيط
    echo "<h3>5. اختبار استعلام بسيط:</h3>";
    try {
        $result = $pdo_db->query("SELECT 1 as test")->fetch();
        echo "<div class='success'>✅ الاستعلام نجح!</div>";
    } catch(PDOException $e) {
        echo "<div class='error'>❌ فشل الاستعلام: " . $e->getMessage() . "</div>";
    }
    
    // اختبار 6: التحقق من الجداول
    echo "<h3>6. الجداول الموجودة:</h3>";
    try {
        $tables = $pdo_db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            echo "<div class='success'>✅ عدد الجداول: " . count($tables) . "</div>";
            echo "<div class='info'>أول 10 جداول:</div>";
            echo "<pre>" . implode("\n", array_slice($tables, 0, 10)) . "</pre>";
        } else {
            echo "<div class='error'>⚠️ قاعدة البيانات فارغة (لا توجد جداول)</div>";
        }
    } catch(PDOException $e) {
        echo "<div class='error'>❌ خطأ في عرض الجداول: " . $e->getMessage() . "</div>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ فشل الاتصال بقاعدة البيانات</div>";
    echo "<pre>الخطأ: " . $e->getMessage() . "</pre>";
    
    // تحليل الخطأ
    if (strpos($e->getMessage(), '1045') !== false || strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<h3>💡 حلول مقترحة:</h3>";
        echo "<ul>";
        if ($host === 'localhost') {
            echo "<li><strong>1. غيّر DB_HOST من 'localhost' إلى '127.0.0.1' في ملف .env</strong></li>";
        }
        echo "<li>2. تحقق من اسم المستخدم في cPanel → MySQL Databases</li>";
        echo "<li>3. تحقق من كلمة المرور (استخدم Copy/Paste مباشرة من cPanel)</li>";
        echo "<li>4. تأكد من أن المستخدم مرتبط بقاعدة البيانات في cPanel</li>";
        echo "<li>5. أضف '127.0.0.1' في Remote MySQL في cPanel</li>";
        echo "</ul>";
    }
}

echo "<hr>";
echo "<p><strong>⚠️ مهم:</strong> احذف هذا الملف بعد حل المشكلة!</p>";
echo "<p><strong>📋 الخطوات التالية:</strong></p>";
echo "<ol>";
echo "<li>إذا نجح الاتصال، المشكلة قد تكون في Laravel - تحقق من <code>php artisan config:clear</code></li>";
echo "<li>إذا فشل الاتصال، اتبع الحلول المقترحة أعلاه</li>";
echo "<li>راجع ملف <code>حل_مشكلة_قاعدة_البيانات_1045.md</code> للحلول التفصيلية</li>";
echo "</ol>";



