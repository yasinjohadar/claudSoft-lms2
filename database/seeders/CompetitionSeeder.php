<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\User;
use Carbon\Carbon;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض الطلاب للتجربة
        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->limit(10)
            ->get();

        if ($students->count() < 2) {
            $this->command->warn('لا يوجد عدد كافٍ من الطلاب لإنشاء منافسات تجريبية.');
            return;
        }

        $competitions = [];

        // منافسة نقاط نشطة
        $pointsCompetition = Competition::create([
            'creator_id' => $students[0]->id,
            'name' => 'منافسة النقاط الأسبوعية',
            'type' => 'points',
            'target_value' => 5000,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->addDays(5),
            'status' => 'active',
        ]);

        // إضافة مشاركين
        for ($i = 0; $i < min(5, $students->count()); $i++) {
            CompetitionParticipant::create([
                'competition_id' => $pointsCompetition->id,
                'user_id' => $students[$i]->id,
                'current_value' => rand(500, 3000),
                'rank' => $i + 1,
                'joined_at' => now()->subDays(2),
            ]);
        }

        $competitions[] = $pointsCompetition;

        // منافسة دروس نشطة
        if ($students->count() >= 4) {
            $lessonsCompetition = Competition::create([
                'creator_id' => $students[1]->id,
                'name' => 'منافسة الدروس',
                'type' => 'lessons',
                'target_value' => 50,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(6),
                'status' => 'active',
            ]);

            for ($i = 1; $i < min(5, $students->count()); $i++) {
                CompetitionParticipant::create([
                    'competition_id' => $lessonsCompetition->id,
                    'user_id' => $students[$i]->id,
                    'current_value' => rand(5, 30),
                    'rank' => $i,
                    'joined_at' => now()->subDay(),
                ]);
            }

            $competitions[] = $lessonsCompetition;
        }

        // منافسة مكتملة
        if ($students->count() >= 3) {
            $completedCompetition = Competition::create([
                'creator_id' => $students[0]->id,
                'name' => 'منافسة XP الأسبوع الماضي',
                'type' => 'xp',
                'target_value' => 2000,
                'starts_at' => now()->subDays(14),
                'ends_at' => now()->subDays(7),
                'status' => 'completed',
                'completed_at' => now()->subDays(7),
            ]);

            // المشارك الفائز
            CompetitionParticipant::create([
                'competition_id' => $completedCompetition->id,
                'user_id' => $students[0]->id,
                'current_value' => 2500,
                'rank' => 1,
                'is_winner' => true,
                'joined_at' => now()->subDays(14),
            ]);

            // مشاركون آخرون
            for ($i = 1; $i < min(4, $students->count()); $i++) {
                CompetitionParticipant::create([
                    'competition_id' => $completedCompetition->id,
                    'user_id' => $students[$i]->id,
                    'current_value' => rand(1000, 2400),
                    'rank' => $i + 1,
                    'is_winner' => false,
                    'joined_at' => now()->subDays(14),
                ]);
            }

            $competitions[] = $completedCompetition;
        }

        $this->command->info('✅ تم إنشاء ' . count($competitions) . ' منافسة تجريبية بنجاح!');
    }
}
