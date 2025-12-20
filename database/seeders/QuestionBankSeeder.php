<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * يقوم بتشغيل جميع seeders الخاصة ببنك الأسئلة
     */
    public function run(): void
    {
        $this->command->info('🚀 بدء إنشاء بنك الأسئلة الشامل...');
        $this->command->newLine();

        // تشغيل seeder لغات البرمجة أولاً
        $this->call(ProgrammingLanguageSeeder::class);
        $this->command->newLine();

        // تشغيل seeders الأسئلة
        $this->call([
            HtmlCssQuestionBankSeeder::class,
            LaravelQuestionBankSeeder::class,
            JavaScriptQuestionBankSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('🎉 تم إنشاء بنك الأسئلة بنجاح!');
        $this->command->info('📊 إجمالي الأسئلة: 120 سؤال');
        $this->command->info('   - HTML & CSS: 20 سؤال');
        $this->command->info('   - Laravel: 50 سؤال');
        $this->command->info('   - JavaScript ES6+: 50 سؤال');
    }
}
