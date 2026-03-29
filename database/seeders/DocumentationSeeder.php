<?php

namespace Database\Seeders;

use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->orderBy('id')->value('id');
        $now = now();

        $catLaravel = DocumentationCategory::updateOrCreate(
            ['slug' => 'laravel'],
            [
                'name' => 'Laravel',
                'description' => 'توثيق إطار عمل Laravel: التثبيت، التوجيه، والمزيد.',
                'icon' => 'fab fa-laravel',
                'kind' => 'technology',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $catPhp = DocumentationCategory::updateOrCreate(
            ['slug' => 'php'],
            [
                'name' => 'PHP',
                'description' => 'أساسيات لغة PHP للمطورين.',
                'icon' => 'fab fa-php',
                'kind' => 'technology',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $catStart = DocumentationCategory::updateOrCreate(
            ['slug' => 'getting-started'],
            [
                'name' => 'البدء السريع',
                'description' => 'دليل استخدام منصة التوثيق والتنقل بين الأقسام.',
                'icon' => 'bi bi-rocket-takeoff',
                'kind' => 'section',
                'parent_id' => null,
                'sort_order' => 0,
                'is_active' => true,
            ]
        );

        $published = [
            'status' => 'published',
            'published_at' => $now,
            'is_indexable' => true,
            'updated_by' => $userId,
        ];

        $page = function (DocumentationCategory $cat, ?int $parentId, string $slug, string $title, string $excerpt, string $html, int $sort) use ($published) {
            return DocumentationPage::updateOrCreate(
                [
                    'documentation_category_id' => $cat->id,
                    'parent_id' => $parentId,
                    'slug' => $slug,
                ],
                array_merge([
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $html,
                    'sort_order' => $sort,
                    'meta_title' => $title,
                    'meta_description' => $excerpt,
                ], $published)
            );
        };

        $page($catStart, null, 'welcome', 'مرحباً بك في التوثيق', 'نظرة عامة على الدليل.', <<<'HTML'
<p>محتوى تجريبي لمعاينة لوحة التحكم.</p>
<ul>
<li>التنقل بين الأقسام من القائمة.</li>
<li>صفحات رئيسية وفرعية مدعومة.</li>
</ul>
HTML, 0);

        $page($catLaravel, null, 'introduction', 'مقدمة Laravel', 'ما هو Laravel.', <<<'HTML'
<p><strong>Laravel</strong> إطار عمل PHP حديث.</p>
<ul><li>Routing</li><li>Eloquent</li><li>Blade</li></ul>
HTML, 1);

        $page($catLaravel, null, 'installation', 'التثبيت والإعداد', 'إنشاء مشروع جديد.', <<<'HTML'
<pre><code>composer create-project laravel/laravel example-app
cd example-app
php artisan serve</code></pre>
HTML, 2);

        $pRouting = $page($catLaravel, null, 'routing', 'التوجيه (Routing)', 'تعريف المسارات.', <<<'HTML'
<pre><code>Route::get('/', function () {
    return view('welcome');
});</code></pre>
HTML, 3);

        $page($catLaravel, $pRouting->id, 'route-parameters', 'معاملات المسارات', 'مسارات ديناميكية.', <<<'HTML'
<pre><code>Route::get('/user/{id}', function (string $id) {
    return 'User '.$id;
});</code></pre>
HTML, 1);

        $page($catPhp, null, 'php-basics', 'أساسيات PHP', 'متغيرات وشروط.', <<<'HTML'
<pre><code>$name = 'ClaudSoft';
if ($x >= 0) { echo $name; }</code></pre>
HTML, 0);

        $page($catPhp, null, 'php-arrays', 'المصفوفات في PHP', 'مثال ترابطي.', <<<'HTML'
<pre><code>$user = ['name' => 'أحمد', 'role' => 'مطور'];</code></pre>
HTML, 1);

        $this->command?->info('DocumentationSeeder: 3 أقسام، 7 صفحات.');
    }
}