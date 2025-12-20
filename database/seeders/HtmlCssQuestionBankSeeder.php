<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Course;
use App\Models\ProgrammingLanguage;
use Illuminate\Support\Facades\DB;

class HtmlCssQuestionBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على كورس HTML & CSS
        $course = Course::where('code', 'WEB-HTML-001')->first();

        if (!$course) {
            $this->command->error('❌ كورس HTML & CSS غير موجود! يرجى تشغيل CourseSeeder أولاً');
            return;
        }

        // الحصول على المستخدم (instructor)
        $instructor = $course->instructor ??  \App\Models\User::first();

        if (!$instructor) {
            $this->command->error('❌ لا يوجد مستخدم لإنشاء الأسئلة!');
            return;
        }

        // الحصول على أنواع الأسئلة
        $trueFalseType = QuestionType::where('name', 'true_false')->first();
        $multipleChoiceType = QuestionType::where('name', 'multiple_choice_single')->first();

        if (!$trueFalseType || !$multipleChoiceType) {
            $this->command->error('❌ أنواع الأسئلة غير موجودة! يرجى تشغيل QuestionTypeSeeder أولاً');
            return;
        }

        // الحصول على لغات البرمجة
        $htmlLang = ProgrammingLanguage::where('slug', 'html')->first();
        $cssLang = ProgrammingLanguage::where('slug', 'css')->first();

        if (!$htmlLang || !$cssLang) {
            $this->command->error('❌ لغات HTML و CSS غير موجودة! يرجى تشغيل ProgrammingLanguageSeeder أولاً');
            return;
        }

        // بدء المعاملة
        DB::beginTransaction();

        try {
            // ========== أسئلة صح وخطأ (10 أسئلة) ==========

            $trueFalseQuestions = [
                [
                    'question_text' => '<p>HTML تعني Hyper Text Markup Language</p>',
                    'correct_answer' => 'true',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'languages' => [$htmlLang->id],
                ],
                [
                    'question_text' => '<p>CSS تعني Computer Style Sheets</p>',
                    'correct_answer' => 'false',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'languages' => [$cssLang->id],
                ],
                [
                    'question_text' => '<p>وسم &lt;br&gt; يستخدم لإنشاء سطر جديد في HTML</p>',
                    'correct_answer' => 'true',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'languages' => [$htmlLang->id],
                ],
                [
                    'question_text' => '<p>يمكن استخدام أكثر من وسم &lt;h1&gt; في نفس الصفحة</p>',
                    'correct_answer' => 'true',
                    'difficulty' => 'medium',
                    'points' => 2,
                    'languages' => [$htmlLang->id],
                ],
                [
                    'question_text' => '<p>وسم &lt;img&gt; يحتاج إلى وسم إغلاق &lt;/img&gt;</p>',
                    'correct_answer' => 'false',
                    'difficulty' => 'medium',
                    'points' => 2,
                    'languages' => [$htmlLang->id],
                ],
                [
                    'question_text' => '<p>خاصية class في HTML يمكن أن تُستخدم مرة واحدة فقط في الصفحة</p>',
                    'correct_answer' => 'false',
                    'difficulty' => 'medium',
                    'points' => 2,
                    'languages' => [$htmlLang->id, $cssLang->id],
                ],
                [
                    'question_text' => '<p>في CSS، يمكن استخدام النقطة (.) لاستهداف العناصر بناءً على class</p>',
                    'correct_answer' => 'true',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'languages' => [$htmlLang->id, $cssLang->id],
                ],
                [
                    'question_text' => '<p>خاصية id في HTML يمكن أن تتكرر لأكثر من عنصر واحد</p>',
                    'correct_answer' => 'false',
                    'difficulty' => 'hard',
                    'points' => 3,
                    'languages' => [$htmlLang->id],
                ],
                [
                    'question_text' => '<p>الخاصية margin في CSS تُستخدم للمسافة الداخلية للعنصر</p>',
                    'correct_answer' => 'false',
                    'difficulty' => 'medium',
                    'points' => 2,
                    'languages' => [$cssLang->id],
                ],
                [
                    'question_text' => '<p>يمكن تضمين CSS داخل ملف HTML باستخدام وسم &lt;style&gt;</p>',
                    'correct_answer' => 'true',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'languages' => [$htmlLang->id, $cssLang->id],
                ],
            ];

            foreach ($trueFalseQuestions as $questionData) {
                $question = QuestionBank::create([
                    'question_type_id' => $trueFalseType->id,
                    'course_id' => $course->id,
                    'question_text' => $questionData['question_text'],
                    'explanation' => null,
                    'difficulty_level' => $questionData['difficulty'],
                    'default_grade' => $questionData['points'],
                    'is_active' => true,
                    'times_used' => 0,
                    'created_by' => $instructor->id,
                ]);

                // ربط السؤال باللغات البرمجية
                $question->programmingLanguages()->attach($questionData['languages']);

                // إنشاء خيارات صح وخطأ
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => '<p>صحيح</p>',
                    'is_correct' => $questionData['correct_answer'] === 'true',
                    'grade_percentage' => $questionData['correct_answer'] === 'true' ? 100 : 0,
                    'option_order' => 1,
                ]);

                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => '<p>خطأ</p>',
                    'is_correct' => $questionData['correct_answer'] === 'false',
                    'grade_percentage' => $questionData['correct_answer'] === 'false' ? 100 : 0,
                    'option_order' => 2,
                ]);
            }

            // ========== أسئلة اختيار من متعدد (10 أسئلة) ==========

            $multipleChoiceQuestions = [
                [
                    'question_text' => '<p>ما هو الوسم الصحيح لإنشاء رابط في HTML؟</p>',
                    'difficulty' => 'easy',
                    'points' => 2,
                    'languages' => [$htmlLang->id],
                    'options' => [
                        ['text' => '<p>&lt;a&gt;</p>', 'is_correct' => true],
                        ['text' => '<p>&lt;link&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;href&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;url&gt;</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>ما هي الخاصية المستخدمة لتغيير لون الخلفية في CSS؟</p>',
                    'difficulty' => 'easy',
                    'points' => 2,
                    'languages' => [$cssLang->id],
                    'options' => [
                        ['text' => '<p>background-color</p>', 'is_correct' => true],
                        ['text' => '<p>bgcolor</p>', 'is_correct' => false],
                        ['text' => '<p>color-background</p>', 'is_correct' => false],
                        ['text' => '<p>bg-color</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>أي من الوسوم التالية يُستخدم لإنشاء قائمة غير مرتبة؟</p>',
                    'difficulty' => 'easy',
                    'points' => 2,
                    'languages' => [$htmlLang->id],
                    'options' => [
                        ['text' => '<p>&lt;ul&gt;</p>', 'is_correct' => true],
                        ['text' => '<p>&lt;ol&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;li&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;list&gt;</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>ما هو الوسم الصحيح لإدراج صورة في HTML؟</p>',
                    'difficulty' => 'easy',
                    'points' => 2,
                    'languages' => [$htmlLang->id],
                    'options' => [
                        ['text' => '<p>&lt;img&gt;</p>', 'is_correct' => true],
                        ['text' => '<p>&lt;image&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;picture&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;src&gt;</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>ما هي وحدة القياس المستخدمة للنسبة المئوية في CSS؟</p>',
                    'difficulty' => 'medium',
                    'points' => 3,
                    'languages' => [$cssLang->id],
                    'options' => [
                        ['text' => '<p>%</p>', 'is_correct' => true],
                        ['text' => '<p>px</p>', 'is_correct' => false],
                        ['text' => '<p>em</p>', 'is_correct' => false],
                        ['text' => '<p>rem</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>ما هو الوسم المستخدم لإنشاء جدول في HTML؟</p>',
                    'difficulty' => 'medium',
                    'points' => 3,
                    'languages' => [$htmlLang->id],
                    'options' => [
                        ['text' => '<p>&lt;table&gt;</p>', 'is_correct' => true],
                        ['text' => '<p>&lt;tab&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;tr&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;td&gt;</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>أي من الخصائص التالية تُستخدم لتغيير حجم الخط في CSS؟</p>',
                    'difficulty' => 'easy',
                    'points' => 2,
                    'languages' => [$cssLang->id],
                    'options' => [
                        ['text' => '<p>font-size</p>', 'is_correct' => true],
                        ['text' => '<p>text-size</p>', 'is_correct' => false],
                        ['text' => '<p>font-style</p>', 'is_correct' => false],
                        ['text' => '<p>size</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>ما هو الوسم المستخدم لإنشاء عنوان رئيسي من المستوى الأول؟</p>',
                    'difficulty' => 'easy',
                    'points' => 2,
                    'languages' => [$htmlLang->id],
                    'options' => [
                        ['text' => '<p>&lt;h1&gt;</p>', 'is_correct' => true],
                        ['text' => '<p>&lt;heading&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;head&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;title&gt;</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>ما هي الطريقة الصحيحة لكتابة تعليق في CSS؟</p>',
                    'difficulty' => 'medium',
                    'points' => 3,
                    'languages' => [$cssLang->id],
                    'options' => [
                        ['text' => '<p>/* هذا تعليق */</p>', 'is_correct' => true],
                        ['text' => '<p>// هذا تعليق</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;!-- هذا تعليق --&gt;</p>', 'is_correct' => false],
                        ['text' => '<p># هذا تعليق</p>', 'is_correct' => false],
                    ],
                ],
                [
                    'question_text' => '<p>ما هو الوسم المستخدم لإنشاء فقرة نصية في HTML؟</p>',
                    'difficulty' => 'easy',
                    'points' => 2,
                    'languages' => [$htmlLang->id],
                    'options' => [
                        ['text' => '<p>&lt;p&gt;</p>', 'is_correct' => true],
                        ['text' => '<p>&lt;paragraph&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;text&gt;</p>', 'is_correct' => false],
                        ['text' => '<p>&lt;para&gt;</p>', 'is_correct' => false],
                    ],
                ],
            ];

            foreach ($multipleChoiceQuestions as $questionData) {
                $question = QuestionBank::create([
                    'question_type_id' => $multipleChoiceType->id,
                    'course_id' => $course->id,
                    'question_text' => $questionData['question_text'],
                    'explanation' => null,
                    'difficulty_level' => $questionData['difficulty'],
                    'default_grade' => $questionData['points'],
                    'is_active' => true,
                    'times_used' => 0,
                    'created_by' => $instructor->id,
                ]);

                // ربط السؤال باللغات البرمجية
                $question->programmingLanguages()->attach($questionData['languages']);

                // إنشاء الخيارات
                foreach ($questionData['options'] as $optionIndex => $option) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $option['text'],
                        'is_correct' => $option['is_correct'],
                        'grade_percentage' => $option['is_correct'] ? 100 : 0,
                        'option_order' => $optionIndex + 1,
                    ]);
                }
            }

            DB::commit();

            $this->command->info('✅ تم إنشاء 20 سؤالاً لكورس HTML & CSS بنجاح!');
            $this->command->info('📊 التوزيع: 10 أسئلة صح/خطأ + 10 أسئلة اختيار من متعدد');
            $this->command->info('📝 مستويات الصعوبة: سهل، متوسط، صعب');
            $this->command->info('🏷️  تم ربط الأسئلة باللغات البرمجية: HTML و CSS');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ حدث خطأ أثناء إنشاء الأسئلة: ' . $e->getMessage());
            throw $e;
        }
    }
}
