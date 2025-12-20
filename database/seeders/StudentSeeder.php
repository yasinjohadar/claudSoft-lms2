<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود دور الطالب
        $studentRole = Role::where('name', 'student')->first();

        if (!$studentRole) {
            $this->command->error('⚠️  دور "student" غير موجود! يرجى تشغيل RolePermissionSeeder أولاً.');
            return;
        }

        // كلمة المرور الموحدة
        $password = Hash::make('123456789');

        // أسماء عربية للطلاب
        $firstNames = [
            'محمد', 'أحمد', 'علي', 'حسن', 'خالد',
            'عمر', 'يوسف', 'عبدالله', 'سعيد', 'فهد',
            'فاطمة', 'مريم', 'نورة', 'سارة', 'ريم',
            'لينا', 'دينا', 'هند', 'عائشة', 'خديجة'
        ];

        $lastNames = [
            'العتيبي', 'الغامدي', 'القحطاني', 'الدوسري', 'الشمري',
            'الزهراني', 'الحربي', 'المطيري', 'العنزي', 'السهلي',
            'العمري', 'الجهني', 'الأحمدي', 'البقمي', 'الشهري',
            'الخالدي', 'الرشيدي', 'السلمي', 'العوفي', 'البلوي'
        ];

        $cities = [
            'الرياض', 'جدة', 'مكة', 'المدينة', 'الدمام',
            'الخبر', 'الطائف', 'تبوك', 'أبها', 'حائل',
            'الجبيل', 'ينبع', 'القطيف', 'الأحساء', 'الباحة',
            'جازان', 'نجران', 'الخرج', 'القصيم', 'عرعر'
        ];

        $genders = ['male', 'female'];

        for ($i = 1; $i <= 20; $i++) {
            $email = "student{$i}@example.com";

            // التحقق من عدم وجود المستخدم مسبقاً
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                $this->command->warn("⚠️  الطالب {$email} موجود مسبقاً، تخطي...");
                continue;
            }

            // اختيار جنس الطالب (من 1-10 ذكور، من 11-20 إناث)
            $gender = $i <= 10 ? 'male' : 'female';

            // دمج الاسم الأول والأخير
            $fullName = $firstNames[$i - 1] . ' ' . $lastNames[$i - 1];

            // إنشاء الطالب
            $student = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => $password,
                'phone' => '05' . str_pad($i, 8, '0', STR_PAD_LEFT), // أرقام تسلسلية
                'gender' => $gender,
                'date_of_birth' => now()->subYears(rand(18, 35))->format('Y-m-d'),
                'nationality_id' => rand(1, 5), // جنسيات عشوائية (يجب أن تكون موجودة)
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // إسناد دور الطالب
            $student->assignRole($studentRole);

            $this->command->info("✅ تم إنشاء الطالب: {$fullName} ({$email})");
        }

        $this->command->info('');
        $this->command->info('🎓 تم إنشاء 20 طالباً بنجاح!');
        $this->command->info('📧 البريد الإلكتروني: student1@example.com إلى student20@example.com');
        $this->command->info('🔑 كلمة المرور لجميع الطلاب: 123456789');
    }
}
