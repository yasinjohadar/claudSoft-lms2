<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure 'student' role exists
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        $students = [
            [
                'name' => 'أحمد محمد العلي',
                'email' => 'ahmed.ali@example.com',
                'phone' => '0501234567',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '2000-05-15',
                'address' => 'الرياض، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'فاطمة السعيد',
                'email' => 'fatima.said@example.com',
                'phone' => '0501234568',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '1999-08-22',
                'address' => 'جدة، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'خالد إبراهيم',
                'email' => 'khaled.ibrahim@example.com',
                'phone' => '0501234569',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '1998-12-10',
                'address' => 'الدمام، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'نورة الحربي',
                'email' => 'noura.alharbi@example.com',
                'phone' => '0501234570',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '2001-03-18',
                'address' => 'مكة المكرمة، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'عمر القحطاني',
                'email' => 'omar.alqahtani@example.com',
                'phone' => '0501234571',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '2000-07-25',
                'address' => 'المدينة المنورة، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'مريم الزهراني',
                'email' => 'mariam.alzahrani@example.com',
                'phone' => '0501234572',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '1999-11-30',
                'address' => 'الطائف، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'سعد المطيري',
                'email' => 'saad.almutairi@example.com',
                'phone' => '0501234573',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '2001-01-12',
                'address' => 'أبها، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'ريم العتيبي',
                'email' => 'reem.alotaibi@example.com',
                'phone' => '0501234574',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '2000-09-05',
                'address' => 'تبوك، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'يوسف السالم',
                'email' => 'yousef.alsalem@example.com',
                'phone' => '0501234575',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '1998-06-20',
                'address' => 'حائل، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'هند الدوسري',
                'email' => 'hind.aldosari@example.com',
                'phone' => '0501234576',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '2001-04-08',
                'address' => 'الخبر، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'محمد الشهري',
                'email' => 'mohammad.alshehri@example.com',
                'phone' => '0501234577',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '1999-02-14',
                'address' => 'القصيم، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'سارة القرني',
                'email' => 'sara.alqarni@example.com',
                'phone' => '0501234578',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '2000-10-28',
                'address' => 'جازان، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'عبدالله النمر',
                'email' => 'abdullah.alnamir@example.com',
                'phone' => '0501234579',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '1998-08-03',
                'address' => 'نجران، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'لينا الخطيب',
                'email' => 'lina.alkhatib@example.com',
                'phone' => '0501234580',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '2001-06-17',
                'address' => 'الباحة، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'طارق الفهد',
                'email' => 'tariq.alfahd@example.com',
                'phone' => '0501234581',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'date_of_birth' => '1999-12-22',
                'address' => 'عرعر، المملكة العربية السعودية',
                'is_active' => true,
            ],
            [
                'name' => 'دانة المنصور',
                'email' => 'dana.almansour@example.com',
                'phone' => '0501234582',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'date_of_birth' => '2000-03-11',
                'address' => 'سكاكا، المملكة العربية السعودية',
                'is_active' => true,
            ],
        ];

        foreach ($students as $studentData) {
            $student = User::create($studentData);
            $student->assignRole($studentRole);
        }

        $this->command->info('تم إنشاء 16 طالب بنجاح!');
    }
}
