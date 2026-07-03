<?php

namespace Database\Seeders;

use App\Models\TelegramMessageTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TelegramMessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        TelegramMessageTemplate::firstOrCreate(
            ['slug' => 'welcome-batch-default'],
            [
                'name' => 'الترحيب بالدفعة — Telegram',
                'body' => "مرحباً {student_name} 👋\n\nتم قبولك في {group_name} — {course_name}.\n\nرابط المجموعة:\n{group_link}",
                'is_active' => true,
                'variables' => ['student_name', 'group_name', 'course_name', 'group_link'],
            ]
        );
    }
}
