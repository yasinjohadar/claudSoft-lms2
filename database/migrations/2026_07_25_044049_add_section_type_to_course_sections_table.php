<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->string('section_type', 32)
                ->nullable()
                ->after('title')
                ->index();
        });

        $rules = [
            'simulator' => ['محاكاة', 'محاكيات', 'تنفيذ', 'simulator'],
            'quiz' => ['اختبار', 'اختبارات', 'إختبار', 'إختبارات', 'امتحان', 'امتحانات', 'quiz'],
            'assignment' => ['واجب', 'واجبات', 'تحدي', 'تحديات', 'مشروع', 'مشاريع', 'assignment', 'challenge'],
            'video' => ['دروس شرح', 'دروس الفيديو', 'فيديو', 'فيديوهات', 'شرح مرئي', 'video'],
            'lesson' => ['شروحات', 'شرح نص', 'نصي', 'نصية', 'ملحق', 'ملحقات', 'روابط', 'مقال', 'توثيق', 'lesson', 'resource'],
        ];

        DB::table('course_sections')->orderBy('id')->chunkById(100, function ($sections) use ($rules) {
            foreach ($sections as $section) {
                $title = mb_strtolower((string) $section->title);
                $type = 'default';

                foreach ($rules as $key => $needles) {
                    foreach ($needles as $needle) {
                        if ($title !== '' && mb_strpos($title, mb_strtolower($needle)) !== false) {
                            $type = $key;
                            break 2;
                        }
                    }
                }

                DB::table('course_sections')
                    ->where('id', $section->id)
                    ->update(['section_type' => $type]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropColumn('section_type');
        });
    }
};
