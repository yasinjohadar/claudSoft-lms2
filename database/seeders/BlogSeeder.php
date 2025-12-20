<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $categories = [
            [
                'name' => 'تطوير الويب',
                'slug' => 'web-development',
                'description' => 'مقالات عن تطوير تطبيقات الويب والتقنيات الحديثة',
                'icon' => 'fa-solid fa-code',
                'color' => '#0555a2',
                'meta_title' => 'مقالات تطوير الويب - دروس وشروحات',
                'meta_description' => 'تعلم تطوير الويب من خلال مقالات ودروس شاملة',
                'is_active' => true,
                'is_featured' => true,
                'order' => 1,
            ],
            [
                'name' => 'البرمجة',
                'slug' => 'programming',
                'description' => 'مقالات عن لغات البرمجة والخوارزميات',
                'icon' => 'fa-solid fa-laptop-code',
                'color' => '#f29125',
                'meta_title' => 'مقالات البرمجة - تعلم البرمجة من الصفر',
                'meta_description' => 'دروس ومقالات شاملة عن لغات البرمجة المختلفة',
                'is_active' => true,
                'is_featured' => true,
                'order' => 2,
            ],
            [
                'name' => 'قواعد البيانات',
                'slug' => 'databases',
                'description' => 'مقالات عن قواعد البيانات وإدارتها',
                'icon' => 'fa-solid fa-database',
                'color' => '#28a745',
                'meta_title' => 'مقالات قواعد البيانات',
                'meta_description' => 'تعلم قواعد البيانات SQL و NoSQL',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'التصميم',
                'slug' => 'design',
                'description' => 'مقالات عن تصميم واجهات المستخدم وتجربة المستخدم',
                'icon' => 'fa-solid fa-palette',
                'color' => '#e83e8c',
                'meta_title' => 'مقالات التصميم - UI/UX',
                'meta_description' => 'دروس في تصميم الواجهات وتجربة المستخدم',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'الأمن السيبراني',
                'slug' => 'cybersecurity',
                'description' => 'مقالات عن أمن المعلومات والحماية الإلكترونية',
                'icon' => 'fa-solid fa-shield-halved',
                'color' => '#dc3545',
                'meta_title' => 'مقالات الأمن السيبراني',
                'meta_description' => 'تعلم أساسيات وأفضل ممارسات الأمن السيبراني',
                'is_active' => true,
                'order' => 5,
            ],
        ];

        foreach ($categories as $categoryData) {
            BlogCategory::create($categoryData);
        }

        // Create Tags
        $tags = [
            ['name' => 'Laravel', 'slug' => 'laravel', 'color' => '#ff2d20'],
            ['name' => 'PHP', 'slug' => 'php', 'color' => '#777bb4'],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'color' => '#f7df1e'],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'color' => '#42b883'],
            ['name' => 'React', 'slug' => 'react', 'color' => '#61dafb'],
            ['name' => 'MySQL', 'slug' => 'mysql', 'color' => '#4479a1'],
            ['name' => 'API', 'slug' => 'api', 'color' => '#009688'],
            ['name' => 'Bootstrap', 'slug' => 'bootstrap', 'color' => '#7952b3'],
            ['name' => 'Git', 'slug' => 'git', 'color' => '#f05032'],
            ['name' => 'Docker', 'slug' => 'docker', 'color' => '#2496ed'],
            ['name' => 'CSS', 'slug' => 'css', 'color' => '#1572b6'],
            ['name' => 'HTML', 'slug' => 'html', 'color' => '#e34f26'],
            ['name' => 'SEO', 'slug' => 'seo', 'color' => '#47a547'],
            ['name' => 'أمان', 'slug' => 'security', 'color' => '#ff6b6b'],
            ['name' => 'أداء', 'slug' => 'performance', 'color' => '#4ecdc4'],
        ];

        foreach ($tags as $tagData) {
            BlogTag::create($tagData);
        }

        // Get first user as author
        $author = User::first();

        // Create Blog Posts
        $posts = [
            [
                'title' => 'دليل شامل لتعلم Laravel 11 من الصفر',
                'slug' => 'laravel-11-complete-guide',
                'excerpt' => 'تعلم Laravel 11، أحدث إصدار من إطار العمل الأشهر لتطوير تطبيقات الويب باستخدام PHP. دليل كامل للمبتدئين.',
                'content' => '<p>Laravel هو إطار عمل PHP الأكثر شهرة وقوة لتطوير تطبيقات الويب الحديثة. في هذا المقال، سنتعرف على كل ما تحتاج لمعرفته عن Laravel 11.</p>

<h2>ما هو Laravel؟</h2>
<p>Laravel هو إطار عمل مفتوح المصدر يتبع نمط MVC (Model-View-Controller) ويوفر مجموعة واسعة من الأدوات والميزات التي تساعد المطورين على بناء تطبيقات ويب قوية وآمنة بسرعة.</p>

<h2>ميزات Laravel 11 الجديدة</h2>
<ul>
    <li>بنية مشروع مبسطة وأكثر نظافة</li>
    <li>تحسينات في الأداء والسرعة</li>
    <li>دعم أفضل لـ PHP 8.3</li>
    <li>تحديثات في نظام التوثيق</li>
    <li>تحسينات في Eloquent ORM</li>
</ul>

<h2>البدء مع Laravel</h2>
<p>لتثبيت Laravel 11، تحتاج أولاً إلى التأكد من أن لديك PHP 8.2 أو أحدث، وComposer مثبت على جهازك.</p>

<pre><code>composer create-project laravel/laravel my-app</code></pre>

<h2>هيكل المشروع</h2>
<p>يتميز Laravel بهيكل واضح ومنظم يسهل على المطورين العمل على المشاريع الكبيرة.</p>

<blockquote>تذكر دائماً: Laravel ليس مجرد إطار عمل، بل هو نظام بيئي كامل لتطوير تطبيقات الويب الاحترافية.</blockquote>

<h2>الخلاصة</h2>
<p>Laravel 11 يقدم تحسينات كبيرة تجعل تطوير تطبيقات الويب أسرع وأسهل. ابدأ رحلتك مع Laravel اليوم!</p>',
                'category_id' => 1,
                'author_id' => $author?->id,
                'meta_title' => 'دليل Laravel 11 الشامل للمبتدئين | تعلم من الصفر',
                'meta_description' => 'دليل كامل وشامل لتعلم Laravel 11 من الصفر حتى الاحتراف. شرح مفصل بالعربية مع أمثلة عملية',
                'meta_keywords' => 'Laravel, Laravel 11, تعلم Laravel, PHP Framework, تطوير ويب',
                'focus_keyword' => 'Laravel 11',
                'og_title' => 'تعلم Laravel 11 من الصفر - دليل شامل',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'is_featured' => true,
                'is_indexable' => true,
                'robots_meta' => 'index,follow',
                'reading_time' => 8,
                'views_count' => 1250,
                'priority' => 10,
            ],
            [
                'title' => 'أفضل ممارسات تصميم قواعد البيانات',
                'slug' => 'best-practices-database-design',
                'excerpt' => 'تعرف على أفضل الممارسات والنصائح لتصميم قواعد بيانات فعالة وقابلة للتوسع.',
                'content' => '<p>تصميم قاعدة البيانات هو أحد أهم جوانب تطوير التطبيقات. تصميم جيد يعني أداء أفضل وسهولة في الصيانة.</p>

<h2>مبادئ التصميم الأساسية</h2>
<p>عند تصميم قاعدة بيانات، يجب مراعاة عدة مبادئ أساسية:</p>

<h3>1. التطبيع (Normalization)</h3>
<p>التطبيع هو عملية تنظيم البيانات لتقليل التكرار وتحسين سلامة البيانات.</p>

<h3>2. الفهرسة (Indexing)</h3>
<p>استخدام الفهارس بشكل صحيح يمكن أن يحسن أداء الاستعلامات بشكل كبير.</p>

<h2>نصائح عملية</h2>
<ul>
    <li>استخدم أنواع البيانات المناسبة لكل عمود</li>
    <li>تجنب استخدام NULL عندما يكون ذلك ممكناً</li>
    <li>استخدم المفاتيح الخارجية لضمان سلامة البيانات</li>
    <li>خطط للنمو المستقبلي</li>
</ul>

<h2>الخلاصة</h2>
<p>تصميم قاعدة بيانات جيد يستغرق وقتاً ولكنه يوفر الكثير من المشاكل لاحقاً.</p>',
                'category_id' => 3,
                'author_id' => $author?->id,
                'meta_title' => 'أفضل ممارسات تصميم قواعد البيانات | دليل شامل',
                'meta_description' => 'تعلم أفضل الممارسات والتقنيات لتصميم قواعد بيانات احترافية وفعالة',
                'meta_keywords' => 'قواعد البيانات, Database Design, SQL, MySQL',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'is_featured' => true,
                'reading_time' => 6,
                'views_count' => 890,
                'priority' => 8,
            ],
            [
                'title' => 'مقدمة إلى React.js للمبتدئين',
                'slug' => 'react-js-introduction-for-beginners',
                'excerpt' => 'تعلم أساسيات React.js، مكتبة JavaScript الأكثر شهرة لبناء واجهات المستخدم التفاعلية.',
                'content' => '<p>React.js هي مكتبة JavaScript تم تطويرها بواسطة Facebook لبناء واجهات مستخدم ديناميكية وتفاعلية.</p>

<h2>لماذا React؟</h2>
<p>React توفر العديد من المزايا:</p>
<ul>
    <li>أداء عالي بفضل Virtual DOM</li>
    <li>Component-based Architecture</li>
    <li>مجتمع ضخم ونظام بيئي غني</li>
    <li>سهولة إعادة استخدام الكود</li>
</ul>

<h2>المفاهيم الأساسية</h2>
<h3>Components</h3>
<p>المكونات هي اللبنات الأساسية في React. كل شيء في React هو مكون.</p>

<h3>Props و State</h3>
<p>Props و State هما الطريقتان الرئيسيتان لإدارة البيانات في React.</p>

<h2>مثال بسيط</h2>
<pre><code>function Welcome(props) {
  return &lt;h1&gt;Hello, {props.name}&lt;/h1&gt;;
}</code></pre>

<p>ابدأ رحلتك مع React اليوم وستكتشف عالماً جديداً من إمكانيات تطوير الواجهات!</p>',
                'category_id' => 1,
                'author_id' => $author?->id,
                'meta_title' => 'مقدمة إلى React.js - دليل المبتدئين الشامل',
                'meta_description' => 'تعلم React.js من الصفر مع شرح مبسط للمبتدئين',
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'reading_time' => 10,
                'views_count' => 2100,
                'priority' => 9,
            ],
            [
                'title' => 'أساسيات الأمن السيبراني للمطورين',
                'slug' => 'cybersecurity-basics-for-developers',
                'excerpt' => 'دليل شامل لأهم ممارسات الأمن السيبراني التي يجب على كل مطور معرفتها.',
                'content' => '<p>الأمن السيبراني ليس مجرد مسؤولية فريق الأمن، بل هو مسؤولية كل مطور.</p>

<h2>OWASP Top 10</h2>
<p>تعرف على أكثر 10 ثغرات أمنية شيوعاً في تطبيقات الويب:</p>
<ul>
    <li>SQL Injection</li>
    <li>Cross-Site Scripting (XSS)</li>
    <li>Broken Authentication</li>
    <li>Sensitive Data Exposure</li>
</ul>

<h2>أفضل الممارسات</h2>
<h3>1. التحقق من المدخلات</h3>
<p>دائماً تحقق من جميع البيانات القادمة من المستخدم.</p>

<h3>2. استخدام HTTPS</h3>
<p>تأكد من تشفير جميع الاتصالات.</p>

<h3>3. تحديث الاعتماديات</h3>
<p>حافظ على تحديث جميع المكتبات والحزم.</p>

<blockquote>الأمن ليس منتجاً، بل هو عملية مستمرة.</blockquote>',
                'category_id' => 5,
                'author_id' => $author?->id,
                'meta_title' => 'أساسيات الأمن السيبراني للمطورين | دليل شامل',
                'meta_description' => 'تعلم أهم ممارسات الأمن السيبراني لحماية تطبيقاتك',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'reading_time' => 12,
                'views_count' => 650,
                'priority' => 7,
            ],
            [
                'title' => 'تحسين أداء تطبيقات الويب',
                'slug' => 'web-performance-optimization',
                'excerpt' => 'تقنيات واستراتيجيات فعالة لتحسين سرعة وأداء تطبيقات الويب الخاصة بك.',
                'content' => '<p>أداء تطبيق الويب له تأثير مباشر على تجربة المستخدم ومعدلات التحويل.</p>

<h2>قياس الأداء</h2>
<p>قبل التحسين، يجب قياس الأداء الحالي باستخدام أدوات مثل:</p>
<ul>
    <li>Google Lighthouse</li>
    <li>WebPageTest</li>
    <li>Chrome DevTools</li>
</ul>

<h2>تقنيات التحسين</h2>
<h3>1. ضغط الأصول</h3>
<p>ضغط CSS و JavaScript والصور.</p>

<h3>2. التخزين المؤقت</h3>
<p>استخدام Browser Caching و CDN.</p>

<h3>3. Lazy Loading</h3>
<p>تحميل المحتوى عند الحاجة فقط.</p>

<h3>4. Code Splitting</h3>
<p>تقسيم الكود إلى حزم أصغر.</p>

<p>كل ميلي ثانية تحسن في السرعة تعني تجربة مستخدم أفضل!</p>',
                'category_id' => 1,
                'author_id' => $author?->id,
                'meta_title' => 'تحسين أداء تطبيقات الويب - دليل عملي',
                'meta_description' => 'تعلم أفضل تقنيات تحسين سرعة وأداء تطبيقات الويب',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'reading_time' => 9,
                'views_count' => 420,
                'priority' => 6,
            ],
        ];

        $createdPosts = [];
        foreach ($posts as $postData) {
            $categoryId = $postData['category_id'];
            $postData['blog_category_id'] = $categoryId;
            unset($postData['category_id']);

            $post = BlogPost::create($postData);
            $createdPosts[] = $post;

            // Calculate reading time
            $post->calculateReadingTime();
        }

        // Attach tags to posts
        $allTags = BlogTag::all();

        // Post 1 - Laravel guide
        $createdPosts[0]->tags()->attach([1, 2, 7]); // Laravel, PHP, API

        // Post 2 - Database
        $createdPosts[1]->tags()->attach([6]); // MySQL

        // Post 3 - React
        $createdPosts[2]->tags()->attach([3, 5, 11, 12]); // JavaScript, React, CSS, HTML

        // Post 4 - Security
        $createdPosts[3]->tags()->attach([2, 14]); // PHP, أمان

        // Post 5 - Performance
        $createdPosts[4]->tags()->attach([3, 11, 12, 15]); // JavaScript, CSS, HTML, أداء

        // Update categories posts count
        foreach (BlogCategory::all() as $category) {
            $category->updatePostsCount();
        }

        // Update tags posts count
        foreach (BlogTag::all() as $tag) {
            $tag->updatePostsCount();
        }

        $this->command->info('✅ تم إنشاء ' . count($posts) . ' مقالة بنجاح!');
        $this->command->info('✅ تم إنشاء ' . count($categories) . ' تصنيف بنجاح!');
        $this->command->info('✅ تم إنشاء ' . count($tags) . ' وسم بنجاح!');
    }
}
