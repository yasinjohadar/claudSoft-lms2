<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 بدء إنشاء الاختبارات...');
        $this->command->newLine();

        // الحصول على المستخدم (admin/instructor)
        $instructor = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first() ?? User::first();

        if (!$instructor) {
            $this->command->error('❌ لا يوجد مستخدم لإنشاء الاختبارات!');
            return;
        }

        DB::beginTransaction();

        try {
            // 1. اختبار Laravel - أساسي
            $laravelCourse = Course::where('code', 'WEB-LAR-001')->first();
            if ($laravelCourse) {
                $laravelQuestions = QuestionBank::where('course_id', $laravelCourse->id)
                    ->where('is_active', true)
                    ->limit(15)
                    ->get();

                if ($laravelQuestions->count() > 0) {
                    $quiz1 = $this->createQuiz([
                        'course_id' => $laravelCourse->id,
                        'title' => 'اختبار Laravel - أساسي',
                        'description' => 'اختبار شامل يغطي أساسيات Laravel',
                        'instructions' => 'اقرأ كل سؤال بعناية واختر الإجابة الصحيحة. الوقت المحدد: 30 دقيقة.',
                        'quiz_type' => 'graded',
                        'passing_grade' => 60.00,
                        'time_limit' => 30,
                        'attempts_allowed' => 3,
                        'shuffle_questions' => true,
                        'shuffle_answers' => true,
                        'show_correct_answers' => true,
                        'show_correct_answers_after' => 'after_graded',
                        'feedback_mode' => 'after_submission',
                        'allow_review' => true,
                        'show_grade_immediately' => true,
                        'available_from' => now(),
                        'due_date' => now()->addDays(7),
                        'available_until' => now()->addDays(14),
                        'is_published' => true,
                        'is_visible' => true,
                        'created_by' => $instructor->id,
                    ], $laravelQuestions);

                    $this->command->info("✅ تم إنشاء اختبار: {$quiz1->title}");
                }
            }

            // 2. اختبار Laravel - متقدم
            if ($laravelCourse) {
                $laravelAdvancedQuestions = QuestionBank::where('course_id', $laravelCourse->id)
                    ->where('is_active', true)
                    ->where('difficulty_level', 'hard')
                    ->limit(20)
                    ->get();

                if ($laravelAdvancedQuestions->count() > 0) {
                    $quiz2 = $this->createQuiz([
                        'course_id' => $laravelCourse->id,
                        'title' => 'اختبار Laravel - متقدم',
                        'description' => 'اختبار متقدم يغطي مواضيع Laravel المتقدمة',
                        'instructions' => 'هذا اختبار صعب يتطلب معرفة عميقة بـ Laravel. الوقت المحدد: 45 دقيقة.',
                        'quiz_type' => 'final_exam',
                        'passing_grade' => 70.00,
                        'time_limit' => 45,
                        'attempts_allowed' => 2,
                        'shuffle_questions' => true,
                        'shuffle_answers' => true,
                        'show_correct_answers' => true,
                        'show_correct_answers_after' => 'after_due',
                        'feedback_mode' => 'after_due',
                        'allow_review' => true,
                        'show_grade_immediately' => false,
                        'available_from' => now()->addDays(7),
                        'due_date' => now()->addDays(14),
                        'available_until' => now()->addDays(21),
                        'is_published' => true,
                        'is_visible' => true,
                        'created_by' => $instructor->id,
                    ], $laravelAdvancedQuestions);

                    $this->command->info("✅ تم إنشاء اختبار: {$quiz2->title}");
                }
            }

            // 3. اختبار HTML & CSS
            $htmlCourse = Course::where('code', 'WEB-HTML-001')->first();
            if ($htmlCourse) {
                $htmlQuestions = QuestionBank::where('course_id', $htmlCourse->id)
                    ->where('is_active', true)
                    ->limit(20)
                    ->get();

                if ($htmlQuestions->count() > 0) {
                    $quiz3 = $this->createQuiz([
                        'course_id' => $htmlCourse->id,
                        'title' => 'اختبار HTML & CSS',
                        'description' => 'اختبار شامل لـ HTML و CSS',
                        'instructions' => 'اختبار يغطي أساسيات HTML و CSS. الوقت المحدد: 25 دقيقة.',
                        'quiz_type' => 'graded',
                        'passing_grade' => 60.00,
                        'time_limit' => 25,
                        'attempts_allowed' => 3,
                        'shuffle_questions' => false,
                        'shuffle_answers' => true,
                        'show_correct_answers' => true,
                        'show_correct_answers_after' => 'immediately',
                        'feedback_mode' => 'immediate',
                        'allow_review' => true,
                        'show_grade_immediately' => true,
                        'available_from' => now(),
                        'due_date' => now()->addDays(5),
                        'available_until' => now()->addDays(10),
                        'is_published' => true,
                        'is_visible' => true,
                        'created_by' => $instructor->id,
                    ], $htmlQuestions);

                    $this->command->info("✅ تم إنشاء اختبار: {$quiz3->title}");
                }
            }

            // 4. اختبار JavaScript
            $jsCourse = Course::where('code', 'WEB-JS-001')->first();
            if ($jsCourse) {
                $jsQuestions = QuestionBank::where('course_id', $jsCourse->id)
                    ->where('is_active', true)
                    ->limit(25)
                    ->get();

                if ($jsQuestions->count() > 0) {
                    $quiz4 = $this->createQuiz([
                        'course_id' => $jsCourse->id,
                        'title' => 'اختبار JavaScript ES6+',
                        'description' => 'اختبار شامل لـ JavaScript الحديث',
                        'instructions' => 'اختبار يغطي ES6+ والميزات المتقدمة. الوقت المحدد: 40 دقيقة.',
                        'quiz_type' => 'graded',
                        'passing_grade' => 65.00,
                        'time_limit' => 40,
                        'attempts_allowed' => 2,
                        'shuffle_questions' => true,
                        'shuffle_answers' => true,
                        'show_correct_answers' => true,
                        'show_correct_answers_after' => 'after_graded',
                        'feedback_mode' => 'after_submission',
                        'allow_review' => true,
                        'show_grade_immediately' => true,
                        'available_from' => now(),
                        'due_date' => now()->addDays(10),
                        'available_until' => now()->addDays(20),
                        'is_published' => true,
                        'is_visible' => true,
                        'created_by' => $instructor->id,
                    ], $jsQuestions);

                    $this->command->info("✅ تم إنشاء اختبار: {$quiz4->title}");
                }
            }

            // 5. اختبار تدريبي Laravel
            if ($laravelCourse) {
                $practiceQuestions = QuestionBank::where('course_id', $laravelCourse->id)
                    ->where('is_active', true)
                    ->where('difficulty_level', 'easy')
                    ->limit(10)
                    ->get();

                if ($practiceQuestions->count() > 0) {
                    $quiz5 = $this->createQuiz([
                        'course_id' => $laravelCourse->id,
                        'title' => 'اختبار تدريبي - Laravel',
                        'description' => 'اختبار تدريبي بدون درجات',
                        'instructions' => 'هذا اختبار تدريبي يمكنك محاولته عدة مرات. لا يوجد حد زمني.',
                        'quiz_type' => 'practice',
                        'passing_grade' => 0,
                        'time_limit' => null,
                        'attempts_allowed' => null,
                        'shuffle_questions' => true,
                        'shuffle_answers' => true,
                        'show_correct_answers' => true,
                        'show_correct_answers_after' => 'immediately',
                        'feedback_mode' => 'immediate',
                        'allow_review' => true,
                        'show_grade_immediately' => true,
                        'available_from' => now(),
                        'due_date' => null,
                        'available_until' => null,
                        'is_published' => true,
                        'is_visible' => true,
                        'created_by' => $instructor->id,
                    ], $practiceQuestions);

                    $this->command->info("✅ تم إنشاء اختبار: {$quiz5->title}");
                }
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info('🎉 تم إنشاء الاختبارات بنجاح!');
            $this->command->info('📊 تم إنشاء 5 اختبارات مختلفة');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ حدث خطأ: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a quiz with questions
     */
    private function createQuiz(array $quizData, $questions)
    {
        // حساب max_score من مجموع درجات الأسئلة
        $maxScore = $questions->sum('default_grade');
        $quizData['max_score'] = $maxScore;

        // إنشاء الاختبار
        $quiz = Quiz::create($quizData);

        // إضافة الأسئلة للاختبار
        $order = 1;
        foreach ($questions as $question) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'question_order' => $order++,
                'question_grade' => $question->default_grade,
                'is_required' => true,
            ]);
        }

        return $quiz;
    }
}

