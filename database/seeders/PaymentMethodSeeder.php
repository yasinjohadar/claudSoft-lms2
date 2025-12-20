<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'نقداً',
                'name_en' => 'Cash',
                'description' => 'الدفع النقدي المباشر',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'بطاقة ائتمان',
                'name_en' => 'Credit Card',
                'description' => 'الدفع ببطاقة الائتمان (Visa, MasterCard)',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'بطاقة مدى',
                'name_en' => 'Mada Card',
                'description' => 'الدفع ببطاقة مدى',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'تحويل بنكي',
                'name_en' => 'Bank Transfer',
                'description' => 'التحويل البنكي المباشر',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Apple Pay',
                'name_en' => 'Apple Pay',
                'description' => 'الدفع عبر Apple Pay',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'STC Pay',
                'name_en' => 'STC Pay',
                'description' => 'الدفع عبر STC Pay',
                'is_active' => true,
                'order' => 6,
            ],
            [
                'name' => 'PayPal',
                'name_en' => 'PayPal',
                'description' => 'الدفع عبر PayPal',
                'is_active' => true,
                'order' => 7,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
