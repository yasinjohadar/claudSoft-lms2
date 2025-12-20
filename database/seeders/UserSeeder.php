<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '0500000001',
            'student_id' => 'ADM001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
        $this->command->info('✅ تم إنشاء مستخدم Admin: admin@gmail.com / 123456789');

        // Create Student User
        $student = User::create([
            'name' => 'Student User',
            'email' => 'student@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '0500000002',
            'student_id' => 'STD001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $student->assignRole('student');
        $this->command->info('✅ تم إنشاء مستخدم Student: student@gmail.com / 123456789');

        $this->command->info('');
        $this->command->info('📝 معلومات تسجيل الدخول:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('👤 Admin:');
        $this->command->info('   Email: admin@gmail.com');
        $this->command->info('   Password: 123456789');
        $this->command->info('   Role: admin');
        $this->command->info('');
        $this->command->info('🎓 Student:');
        $this->command->info('   Email: student@gmail.com');
        $this->command->info('   Password: 123456789');
        $this->command->info('   Role: student');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
