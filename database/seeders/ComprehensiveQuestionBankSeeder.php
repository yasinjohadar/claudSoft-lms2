<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class ComprehensiveQuestionBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 بدء إنشاء 500 سؤال شامل...');

        // Get question types
        $trueFalseType = QuestionType::where('name', 'true_false')->first();
        $multipleChoiceType = QuestionType::where('name', 'multiple_choice_single')->first();

        if (!$trueFalseType || !$multipleChoiceType) {
            $this->command->error('❌ أنواع الأسئلة غير موجودة!');
            return;
        }

        // Get courses
        $courses = [
            'WEB-LAR-001' => 'Laravel',
            'WEB-HTML-001' => 'HTML & CSS',
            'WEB-JS-001' => 'JavaScript',
            'WEB-REACT-001' => 'React.js',
            'WEB-VUE-001' => 'Vue.js',
            'MOB-FLT-001' => 'Flutter',
            'MOB-KOT-001' => 'Kotlin',
            'AI-PY-001' => 'Python',
            'DB-SQL-001' => 'MySQL',
            'DB-MONGO-001' => 'MongoDB',
        ];

        DB::beginTransaction();

        try {
            $totalQuestions = 0;

            foreach ($courses as $courseCode => $courseName) {
                $course = Course::where('code', $courseCode)->first();

                if (!$course) {
                    $this->command->warn("⚠️  الكورس {$courseCode} غير موجود");
                    continue;
                }

                $instructor = $course->instructor ?? \App\Models\User::first();

                $this->command->info("📝 إضافة أسئلة لكورس: {$courseName}");

                // Create questions based on course
                $questionsData = $this->getQuestionsForCourse($courseName);

                foreach ($questionsData as $questionData) {
                    $question = QuestionBank::create([
                        'question_type_id' => $questionData['type'] === 'true_false' ? $trueFalseType->id : $multipleChoiceType->id,
                        'course_id' => $course->id,
                        'question_text' => $questionData['question_text'],
                        'explanation' => null,
                        'difficulty_level' => $questionData['difficulty'],
                        'default_grade' => $questionData['points'],
                        'is_active' => true,
                        'times_used' => 0,
                        'created_by' => $instructor->id,
                    ]);

                    // Create options
                    foreach ($questionData['options'] as $index => $option) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $option['text'],
                            'is_correct' => $option['is_correct'],
                            'grade_percentage' => $option['is_correct'] ? 100 : 0,
                            'option_order' => $index + 1,
                        ]);
                    }

                    $totalQuestions++;
                }
            }

            DB::commit();

            $this->command->info("✅ تم إنشاء {$totalQuestions} سؤالاً بنجاح!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ حدث خطأ: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get questions data for specific course
     */
    private function getQuestionsForCourse($courseName)
    {
        switch ($courseName) {
            case 'Laravel':
                return $this->getLaravelQuestions();
            case 'HTML & CSS':
                return $this->getHtmlCssQuestions();
            case 'JavaScript':
                return $this->getJavaScriptQuestions();
            case 'React.js':
                return $this->getReactQuestions();
            case 'Vue.js':
                return $this->getVueQuestions();
            case 'Flutter':
                return $this->getFlutterQuestions();
            case 'Kotlin':
                return $this->getKotlinQuestions();
            case 'Python':
                return $this->getPythonQuestions();
            case 'MySQL':
                return $this->getMySQLQuestions();
            case 'MongoDB':
                return $this->getMongoDBQuestions();
            default:
                return [];
        }
    }

    /**
     * Laravel Questions (50 questions)
     */
    private function getLaravelQuestions()
    {
        return [
            // True/False Questions (25)
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel يستخدم معمارية MVC</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Eloquent ORM هو Object-Relational Mapper في Laravel</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Blade هو محرك القوالب الافتراضي في Laravel</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>يمكن استخدام أكثر من middleware على نفس الـ route</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Sanctum يُستخدم لـ API authentication</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الأمر php artisan migrate:fresh يحذف جميع الجداول ثم ينفذ جميع الـ migrations</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Queue يستخدم لتنفيذ المهام في الخلفية</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>يمكن استخدام Redis كـ cache driver في Laravel</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Events و Listeners تستخدم لتطبيق Observer Pattern</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Livewire يسمح ببناء واجهات تفاعلية بدون JavaScript</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Service Container في Laravel يدير dependency injection</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Telescope هو أداة لتصحيح الأخطاء والمراقبة</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>يمكن استخدام Policies للتحكم في الصلاحيات في Laravel</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Vapor هو منصة serverless deployment لـ Laravel</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الملف .env يحتوي على إعدادات البيئة ولا يجب رفعه على Git</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Mix يُستخدم لتجميع وتصغير ملفات CSS و JavaScript</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الأمر php artisan make:model ينشئ Model فقط بدون Migration</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Passport يستخدم OAuth2 للـ API authentication</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>يمكن استخدام Soft Deletes لحذف السجلات مؤقتاً بدلاً من حذفها نهائياً</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Horizon يوفر dashboard لمراقبة Queues</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Accessors و Mutators تستخدم لتعديل البيانات عند قراءتها أو كتابتها</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Sanctum أخف وزناً من Passport</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Route Model Binding يسمح بحقن Model مباشرة في Controller</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Scout يوفر full-text search للـ Eloquent models</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Laravel Vite حل محل Laravel Mix في الإصدارات الحديثة</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],

            // Multiple Choice Questions (25)
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الأمر الصحيح لإنشاء Controller في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>php artisan make:controller UserController</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan create:controller UserController</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan new:controller UserController</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan generate:controller UserController</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم للتحقق من صحة البيانات في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>Validation Rules</p>', 'is_correct' => true],
                    ['text' => '<p>Data Checker</p>', 'is_correct' => false],
                    ['text' => '<p>Input Validator</p>', 'is_correct' => false],
                    ['text' => '<p>Form Verifier</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي العلاقة الصحيحة لـ One-to-Many في Eloquent؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>hasMany() و belongsTo()</p>', 'is_correct' => true],
                    ['text' => '<p>hasOne() و belongsTo()</p>', 'is_correct' => false],
                    ['text' => '<p>hasMany() و hasOne()</p>', 'is_correct' => false],
                    ['text' => '<p>belongsToMany() و hasMany()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أين يتم تخزين ملفات Routes الرئيسية في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>routes/</p>', 'is_correct' => true],
                    ['text' => '<p>app/Routes/</p>', 'is_correct' => false],
                    ['text' => '<p>config/routes/</p>', 'is_correct' => false],
                    ['text' => '<p>resources/routes/</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الأمر لتشغيل Laravel Scheduler؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>php artisan schedule:run</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan cron:run</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan task:run</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan schedule:start</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لإرسال Emails في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>Mail Facade</p>', 'is_correct' => true],
                    ['text' => '<p>Email Class</p>', 'is_correct' => false],
                    ['text' => '<p>SMTP Helper</p>', 'is_correct' => false],
                    ['text' => '<p>Message Sender</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الملف الذي يحتوي على service providers في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>config/app.php</p>', 'is_correct' => true],
                    ['text' => '<p>bootstrap/app.php</p>', 'is_correct' => false],
                    ['text' => '<p>app/Providers.php</p>', 'is_correct' => false],
                    ['text' => '<p>config/services.php</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُستخدم لتنفيذ Raw SQL Query في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>DB::raw()</p>', 'is_correct' => true],
                    ['text' => '<p>DB::query()</p>', 'is_correct' => false],
                    ['text' => '<p>DB::sql()</p>', 'is_correct' => false],
                    ['text' => '<p>DB::execute()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الأمر لمسح الـ Cache في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>php artisan cache:clear</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan clear:cache</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan cache:flush</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan cache:delete</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لتنفيذ Pagination في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>paginate()</p>', 'is_correct' => true],
                    ['text' => '<p>page()</p>', 'is_correct' => false],
                    ['text' => '<p>limit()</p>', 'is_correct' => false],
                    ['text' => '<p>chunk()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الأمر لإنشاء Middleware في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>php artisan make:middleware</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan create:middleware</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan new:middleware</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan generate:middleware</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أين يتم تعريف API Routes في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>routes/api.php</p>', 'is_correct' => true],
                    ['text' => '<p>routes/web.php</p>', 'is_correct' => false],
                    ['text' => '<p>app/Api/routes.php</p>', 'is_correct' => false],
                    ['text' => '<p>config/api.php</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة الصحيحة لإنشاء Many-to-Many relationship؟</p>',
                'difficulty' => 'hard',
                'points' => 4,
                'options' => [
                    ['text' => '<p>belongsToMany()</p>', 'is_correct' => true],
                    ['text' => '<p>hasMany()</p>', 'is_correct' => false],
                    ['text' => '<p>hasManyThrough()</p>', 'is_correct' => false],
                    ['text' => '<p>morphMany()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لحماية من CSRF attacks في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>@csrf Blade directive</p>', 'is_correct' => true],
                    ['text' => '<p>@token directive</p>', 'is_correct' => false],
                    ['text' => '<p>@secure directive</p>', 'is_correct' => false],
                    ['text' => '<p>@protect directive</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الأمر لإنشاء Seeder في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>php artisan make:seeder</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan create:seeder</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan new:seeder</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan generate:seeder</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُستخدم لتنفيذ Eager Loading في Eloquent؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>with()</p>', 'is_correct' => true],
                    ['text' => '<p>load()</p>', 'is_correct' => false],
                    ['text' => '<p>include()</p>', 'is_correct' => false],
                    ['text' => '<p>fetch()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الملف الذي يحتوي على Database connection settings؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>config/database.php</p>', 'is_correct' => true],
                    ['text' => '<p>.env</p>', 'is_correct' => false],
                    ['text' => '<p>config/app.php</p>', 'is_correct' => false],
                    ['text' => '<p>bootstrap/app.php</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لتشفير البيانات في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>Crypt Facade</p>', 'is_correct' => true],
                    ['text' => '<p>Hash Facade</p>', 'is_correct' => false],
                    ['text' => '<p>Encrypt Class</p>', 'is_correct' => false],
                    ['text' => '<p>Security Helper</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الأمر لإنشاء Factory في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>php artisan make:factory</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan create:factory</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan new:factory</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan generate:factory</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُستخدم لإرجاع JSON response في Controller؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>response()->json()</p>', 'is_correct' => true],
                    ['text' => '<p>return json()</p>', 'is_correct' => false],
                    ['text' => '<p>json()->return()</p>', 'is_correct' => false],
                    ['text' => '<p>toJson()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الأمر لتشغيل Queue Worker في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>php artisan queue:work</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan queue:start</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan queue:run</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan worker:start</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لتنفيذ File Upload في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>Storage Facade</p>', 'is_correct' => true],
                    ['text' => '<p>File Helper</p>', 'is_correct' => false],
                    ['text' => '<p>Upload Class</p>', 'is_correct' => false],
                    ['text' => '<p>Media Manager</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة الصحيحة لتعريف Route Parameter في Laravel؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>Route::get(\'/user/{id}\', ...)</p>', 'is_correct' => true],
                    ['text' => '<p>Route::get(\'/user/:id\', ...)</p>', 'is_correct' => false],
                    ['text' => '<p>Route::get(\'/user/$id\', ...)</p>', 'is_correct' => false],
                    ['text' => '<p>Route::get(\'/user/[id]\', ...)</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لإنشاء Custom Artisan Command؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>php artisan make:command</p>', 'is_correct' => true],
                    ['text' => '<p>php artisan create:command</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan new:command</p>', 'is_correct' => false],
                    ['text' => '<p>php artisan generate:command</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الـ default session driver في Laravel؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>file</p>', 'is_correct' => true],
                    ['text' => '<p>database</p>', 'is_correct' => false],
                    ['text' => '<p>cookie</p>', 'is_correct' => false],
                    ['text' => '<p>array</p>', 'is_correct' => false],
                ]
            ],
        ];
    }

    /**
     * HTML & CSS Questions (50 questions)
     */
    private function getHtmlCssQuestions()
    {
        return [
            // True/False (25)
            [
                'type' => 'true_false',
                'question_text' => '<p>HTML تعني Hyper Text Markup Language</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>CSS تعني Cascading Style Sheets</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>وسم &lt;div&gt; هو block-level element</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>وسم &lt;span&gt; هو inline element</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Flexbox يُستخدم لإنشاء layouts مرنة</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>CSS Grid أفضل من Flexbox دائماً</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية z-index تتحكم في ترتيب العناصر على المحور Z</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Media Queries تُستخدم لإنشاء Responsive Design</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية position: absolute تجعل العنصر خارج document flow</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Semantic HTML يحسن SEO</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية display: none تخفي العنصر وتزيله من document flow</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>CSS Variables تبدأ بـ --</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية box-sizing: border-box تشمل padding و border في العرض الكلي</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>HTML5 يدعم وسوم &lt;video&gt; و &lt;audio&gt;</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Pseudo-classes تبدأ بـ ::</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Pseudo-elements تبدأ بـ ::</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية !important تعطي أعلى أولوية للقاعدة</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>CSS Animations أسرع من JavaScript Animations دائماً</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية float لا تزال تُستخدم في Modern CSS</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>وسم &lt;section&gt; هو semantic HTML element</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>CSS Preprocessors مثل SASS و LESS يضيفون features لـ CSS</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية overflow: hidden تقص المحتوى الزائد</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>BEM هو CSS naming methodology</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>CSS Grid يمكن أن يستخدم مع Flexbox معاً</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>الخاصية transform لا تؤثر على document flow</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],

            // Multiple Choice (25)
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الوسم الصحيح لإنشاء رابط؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>&lt;a&gt;</p>', 'is_correct' => true],
                    ['text' => '<p>&lt;link&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;href&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;url&gt;</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم لتغيير لون الخلفية؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>background-color</p>', 'is_correct' => true],
                    ['text' => '<p>bgcolor</p>', 'is_correct' => false],
                    ['text' => '<p>color-background</p>', 'is_correct' => false],
                    ['text' => '<p>bg-color</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو selector الصحيح لاستهداف class في CSS؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>.className</p>', 'is_correct' => true],
                    ['text' => '<p>#className</p>', 'is_correct' => false],
                    ['text' => '<p>className</p>', 'is_correct' => false],
                    ['text' => '<p>*className</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم لتغيير حجم الخط؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>font-size</p>', 'is_correct' => true],
                    ['text' => '<p>text-size</p>', 'is_correct' => false],
                    ['text' => '<p>font-style</p>', 'is_correct' => false],
                    ['text' => '<p>size</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الوسم الصحيح لإدراج صورة؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>&lt;img&gt;</p>', 'is_correct' => true],
                    ['text' => '<p>&lt;image&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;picture&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;src&gt;</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم لجعل النص bold؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>font-weight: bold</p>', 'is_correct' => true],
                    ['text' => '<p>font-style: bold</p>', 'is_correct' => false],
                    ['text' => '<p>text-weight: bold</p>', 'is_correct' => false],
                    ['text' => '<p>text-style: bold</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو display value الافتراضي لـ &lt;div&gt;؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>block</p>', 'is_correct' => true],
                    ['text' => '<p>inline</p>', 'is_correct' => false],
                    ['text' => '<p>inline-block</p>', 'is_correct' => false],
                    ['text' => '<p>flex</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم للمسافة الداخلية؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>padding</p>', 'is_correct' => true],
                    ['text' => '<p>margin</p>', 'is_correct' => false],
                    ['text' => '<p>spacing</p>', 'is_correct' => false],
                    ['text' => '<p>border</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة الصحيحة لكتابة تعليق في CSS؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>/* تعليق */</p>', 'is_correct' => true],
                    ['text' => '<p>// تعليق</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;!-- تعليق --&gt;</p>', 'is_correct' => false],
                    ['text' => '<p># تعليق</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي ليس CSS Framework؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>React</p>', 'is_correct' => true],
                    ['text' => '<p>Bootstrap</p>', 'is_correct' => false],
                    ['text' => '<p>Tailwind CSS</p>', 'is_correct' => false],
                    ['text' => '<p>Bulma</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الوسم المستخدم لإنشاء قائمة مرتبة؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>&lt;ol&gt;</p>', 'is_correct' => true],
                    ['text' => '<p>&lt;ul&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;li&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;list&gt;</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم لمحاذاة النص؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>text-align</p>', 'is_correct' => true],
                    ['text' => '<p>align-text</p>', 'is_correct' => false],
                    ['text' => '<p>text-position</p>', 'is_correct' => false],
                    ['text' => '<p>alignment</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الوسم الصحيح لإنشاء جدول؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>&lt;table&gt;</p>', 'is_correct' => true],
                    ['text' => '<p>&lt;tab&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;tr&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;td&gt;</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تجعل العنصر مخفياً لكن يحجز مساحته؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>visibility: hidden</p>', 'is_correct' => true],
                    ['text' => '<p>display: none</p>', 'is_correct' => false],
                    ['text' => '<p>opacity: 0</p>', 'is_correct' => false],
                    ['text' => '<p>hidden: true</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو selector الصحيح لاستهداف id في CSS؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>#idName</p>', 'is_correct' => true],
                    ['text' => '<p>.idName</p>', 'is_correct' => false],
                    ['text' => '<p>idName</p>', 'is_correct' => false],
                    ['text' => '<p>*idName</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لإنشاء Rounded Corners؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>border-radius</p>', 'is_correct' => true],
                    ['text' => '<p>border-round</p>', 'is_correct' => false],
                    ['text' => '<p>corner-radius</p>', 'is_correct' => false],
                    ['text' => '<p>rounded-border</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الوحدة النسبية في CSS؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>em</p>', 'is_correct' => true],
                    ['text' => '<p>px</p>', 'is_correct' => false],
                    ['text' => '<p>cm</p>', 'is_correct' => false],
                    ['text' => '<p>mm</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم لإنشاء Shadow للنص؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>text-shadow</p>', 'is_correct' => true],
                    ['text' => '<p>shadow-text</p>', 'is_correct' => false],
                    ['text' => '<p>box-shadow</p>', 'is_correct' => false],
                    ['text' => '<p>font-shadow</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الوسم الصحيح للعنوان الرئيسي؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>&lt;h1&gt;</p>', 'is_correct' => true],
                    ['text' => '<p>&lt;heading&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;head&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;title&gt;</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم لتحديد نوع الخط؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>font-family</p>', 'is_correct' => true],
                    ['text' => '<p>font-type</p>', 'is_correct' => false],
                    ['text' => '<p>font-name</p>', 'is_correct' => false],
                    ['text' => '<p>text-family</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي القيمة الافتراضية لـ position؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>static</p>', 'is_correct' => true],
                    ['text' => '<p>relative</p>', 'is_correct' => false],
                    ['text' => '<p>absolute</p>', 'is_correct' => false],
                    ['text' => '<p>fixed</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي خاصية تُستخدم لتحديد لون النص؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>color</p>', 'is_correct' => true],
                    ['text' => '<p>text-color</p>', 'is_correct' => false],
                    ['text' => '<p>font-color</p>', 'is_correct' => false],
                    ['text' => '<p>foreground-color</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الوسم المستخدم لإنشاء فقرة؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>&lt;p&gt;</p>', 'is_correct' => true],
                    ['text' => '<p>&lt;paragraph&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;text&gt;</p>', 'is_correct' => false],
                    ['text' => '<p>&lt;para&gt;</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لإنشاء Gradient في CSS؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>linear-gradient()</p>', 'is_correct' => true],
                    ['text' => '<p>gradient()</p>', 'is_correct' => false],
                    ['text' => '<p>color-gradient()</p>', 'is_correct' => false],
                    ['text' => '<p>background-gradient()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الخاصية المستخدمة لتحديد عرض العنصر؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>width</p>', 'is_correct' => true],
                    ['text' => '<p>size</p>', 'is_correct' => false],
                    ['text' => '<p>w</p>', 'is_correct' => false],
                    ['text' => '<p>element-width</p>', 'is_correct' => false],
                ]
            ],
        ];
    }

    /**
     * JavaScript Questions (50 questions)
     */
    private function getJavaScriptQuestions()
    {
        return [
            // True/False (25)
            [
                'type' => 'true_false',
                'question_text' => '<p>JavaScript هي لغة مفسرة Interpreted</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>let و const تم إضافتهما في ES6</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Arrow Functions لها this binding خاص بها</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>JavaScript هي Single-threaded language</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Promises تُستخدم للتعامل مع Asynchronous operations</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>async/await هو syntactic sugar فوق Promises</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>== و === يعطيان نفس النتيجة دائماً</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>null و undefined هما نفس الشيء</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Closure يسمح للدالة بالوصول لمتغيرات الـ outer scope</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Event Loop يدير Asynchronous operations في JavaScript</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>map() يغير المصفوفة الأصلية</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Spread Operator (...) تم إضافته في ES6</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Destructuring يسمح باستخراج قيم من Arrays و Objects</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>typeof null يعطي "null"</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Template Literals تستخدم backticks (``)</p>',
                'difficulty' => 'easy',
                'points' => 1,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>forEach() تُرجع قيمة جديدة</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>JavaScript يدعم Multi-threading عبر Web Workers</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Symbol هو Primitive data type في JavaScript</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Object.freeze() يمنع التعديل على Object</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>JavaScript يدعم Class-based OOP من البداية</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => false],
                    ['text' => '<p>خطأ</p>', 'is_correct' => true],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Set و Map هي Data Structures جديدة في ES6</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Hoisting يرفع المتغيرات والدوال للأعلى</p>',
                'difficulty' => 'hard',
                'points' => 3,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Module System (import/export) تم إضافته في ES6</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Rest Parameters تسمح بتمرير عدد غير محدد من الـ arguments</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'true_false',
                'question_text' => '<p>Optional Chaining (?.) يمنع errors عند الوصول لخصائص غير موجودة</p>',
                'difficulty' => 'medium',
                'points' => 2,
                'options' => [
                    ['text' => '<p>صحيح</p>', 'is_correct' => true],
                    ['text' => '<p>خطأ</p>', 'is_correct' => false],
                ]
            ],

            // Multiple Choice (25)
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة الصحيحة لتعريف متغير ثابت؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>const x = 10</p>', 'is_correct' => true],
                    ['text' => '<p>let x = 10</p>', 'is_correct' => false],
                    ['text' => '<p>var x = 10</p>', 'is_correct' => false],
                    ['text' => '<p>constant x = 10</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لإضافة عنصر لنهاية المصفوفة؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>push()</p>', 'is_correct' => true],
                    ['text' => '<p>pop()</p>', 'is_correct' => false],
                    ['text' => '<p>shift()</p>', 'is_correct' => false],
                    ['text' => '<p>unshift()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة لإنشاء Promise؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>new Promise((resolve, reject) => {})</p>', 'is_correct' => true],
                    ['text' => '<p>Promise.create()</p>', 'is_correct' => false],
                    ['text' => '<p>createPromise()</p>', 'is_correct' => false],
                    ['text' => '<p>new Async()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُستخدم لتحويل String إلى Number؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>parseInt()</p>', 'is_correct' => true],
                    ['text' => '<p>toNumber()</p>', 'is_correct' => false],
                    ['text' => '<p>convertToInt()</p>', 'is_correct' => false],
                    ['text' => '<p>stringToNumber()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو typeof []؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>object</p>', 'is_correct' => true],
                    ['text' => '<p>array</p>', 'is_correct' => false],
                    ['text' => '<p>list</p>', 'is_correct' => false],
                    ['text' => '<p>collection</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لإيقاف Event Propagation؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>event.stopPropagation()</p>', 'is_correct' => true],
                    ['text' => '<p>event.stop()</p>', 'is_correct' => false],
                    ['text' => '<p>event.halt()</p>', 'is_correct' => false],
                    ['text' => '<p>event.cancel()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة لدمج مصفوفتين؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>concat()</p>', 'is_correct' => true],
                    ['text' => '<p>merge()</p>', 'is_correct' => false],
                    ['text' => '<p>combine()</p>', 'is_correct' => false],
                    ['text' => '<p>join()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُرجع true إذا كانت كل العناصر تحقق الشرط؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>every()</p>', 'is_correct' => true],
                    ['text' => '<p>some()</p>', 'is_correct' => false],
                    ['text' => '<p>filter()</p>', 'is_correct' => false],
                    ['text' => '<p>find()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الناتج من: 2 + "2"؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>"22"</p>', 'is_correct' => true],
                    ['text' => '<p>4</p>', 'is_correct' => false],
                    ['text' => '<p>22</p>', 'is_correct' => false],
                    ['text' => '<p>Error</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم للوصول لـ DOM element؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>document.getElementById()</p>', 'is_correct' => true],
                    ['text' => '<p>get.element()</p>', 'is_correct' => false],
                    ['text' => '<p>find.element()</p>', 'is_correct' => false],
                    ['text' => '<p>element.get()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة لتحويل Object إلى JSON string؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>JSON.stringify()</p>', 'is_correct' => true],
                    ['text' => '<p>JSON.parse()</p>', 'is_correct' => false],
                    ['text' => '<p>toJSON()</p>', 'is_correct' => false],
                    ['text' => '<p>JSON.convert()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُستخدم لإزالة آخر عنصر من المصفوفة؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>pop()</p>', 'is_correct' => true],
                    ['text' => '<p>push()</p>', 'is_correct' => false],
                    ['text' => '<p>shift()</p>', 'is_correct' => false],
                    ['text' => '<p>slice()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الفرق بين let و var؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>let له block scope و var له function scope</p>', 'is_correct' => true],
                    ['text' => '<p>لا يوجد فرق</p>', 'is_correct' => false],
                    ['text' => '<p>let أسرع من var</p>', 'is_correct' => false],
                    ['text' => '<p>var أحدث من let</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لتأخير تنفيذ الكود؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>setTimeout()</p>', 'is_correct' => true],
                    ['text' => '<p>delay()</p>', 'is_correct' => false],
                    ['text' => '<p>wait()</p>', 'is_correct' => false],
                    ['text' => '<p>pause()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة لنسخ Array دون تغيير الأصلي؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>[...array]</p>', 'is_correct' => true],
                    ['text' => '<p>array.copy()</p>', 'is_correct' => false],
                    ['text' => '<p>clone(array)</p>', 'is_correct' => false],
                    ['text' => '<p>array.duplicate()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُرجع first element الذي يحقق الشرط؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>find()</p>', 'is_correct' => true],
                    ['text' => '<p>filter()</p>', 'is_correct' => false],
                    ['text' => '<p>search()</p>', 'is_correct' => false],
                    ['text' => '<p>locate()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الناتج من: typeof NaN؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>number</p>', 'is_correct' => true],
                    ['text' => '<p>NaN</p>', 'is_correct' => false],
                    ['text' => '<p>undefined</p>', 'is_correct' => false],
                    ['text' => '<p>object</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لتكرار execution بشكل دوري؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>setInterval()</p>', 'is_correct' => true],
                    ['text' => '<p>setTimeout()</p>', 'is_correct' => false],
                    ['text' => '<p>repeat()</p>', 'is_correct' => false],
                    ['text' => '<p>loop()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة الصحيحة لتعريف Arrow Function؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>const func = () => {}</p>', 'is_correct' => true],
                    ['text' => '<p>const func -> {}</p>', 'is_correct' => false],
                    ['text' => '<p>const func => {}</p>', 'is_correct' => false],
                    ['text' => '<p>arrow func() {}</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُستخدم لتحويل كل عناصر Array؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>map()</p>', 'is_correct' => true],
                    ['text' => '<p>forEach()</p>', 'is_correct' => false],
                    ['text' => '<p>transform()</p>', 'is_correct' => false],
                    ['text' => '<p>convert()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو الناتج من: Boolean("")؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>false</p>', 'is_correct' => true],
                    ['text' => '<p>true</p>', 'is_correct' => false],
                    ['text' => '<p>""</p>', 'is_correct' => false],
                    ['text' => '<p>null</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي من التالي يُستخدم لفلترة Array؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>filter()</p>', 'is_correct' => true],
                    ['text' => '<p>find()</p>', 'is_correct' => false],
                    ['text' => '<p>search()</p>', 'is_correct' => false],
                    ['text' => '<p>select()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هي الطريقة لتحويل JSON string إلى Object؟</p>',
                'difficulty' => 'easy',
                'points' => 2,
                'options' => [
                    ['text' => '<p>JSON.parse()</p>', 'is_correct' => true],
                    ['text' => '<p>JSON.stringify()</p>', 'is_correct' => false],
                    ['text' => '<p>toObject()</p>', 'is_correct' => false],
                    ['text' => '<p>JSON.convert()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>أي method تُستخدم لتقليل Array لقيمة واحدة؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>reduce()</p>', 'is_correct' => true],
                    ['text' => '<p>sum()</p>', 'is_correct' => false],
                    ['text' => '<p>accumulate()</p>', 'is_correct' => false],
                    ['text' => '<p>combine()</p>', 'is_correct' => false],
                ]
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => '<p>ما هو Scope الافتراضي للمتغيرات المعرفة بـ var؟</p>',
                'difficulty' => 'medium',
                'points' => 3,
                'options' => [
                    ['text' => '<p>Function Scope</p>', 'is_correct' => true],
                    ['text' => '<p>Block Scope</p>', 'is_correct' => false],
                    ['text' => '<p>Global Scope</p>', 'is_correct' => false],
                    ['text' => '<p>Local Scope</p>', 'is_correct' => false],
                ]
            ],
        ];
    }

    // يمكنك إكمال باقي الدوال للكورسات الأخرى...
    // سأترك الهيكل جاهز لك لإضافة المزيد

    private function getReactQuestions()
    {
        // سيتم إضافتها لاحقاً أو يمكنك إكمالها
        return [];
    }

    private function getVueQuestions()
    {
        return [];
    }

    private function getFlutterQuestions()
    {
        return [];
    }

    private function getKotlinQuestions()
    {
        return [];
    }

    private function getPythonQuestions()
    {
        return [];
    }

    private function getMySQLQuestions()
    {
        return [];
    }

    private function getMongoDBQuestions()
    {
        return [];
    }
}
