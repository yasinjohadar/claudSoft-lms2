<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            
            // معلومات الاتصال الأساسية
            $table->string('page_title')->default('اتصل بنا');
            $table->text('page_subtitle')->nullable();
            
            // العنوان
            $table->string('address_title')->default('العنوان');
            $table->text('address_text')->nullable();
            $table->string('address_icon')->default('fa-location-dot');
            
            // الهاتف
            $table->string('phone_title')->default('الهاتف');
            $table->json('phone_numbers')->nullable(); // [{"number": "+966551966588", "label": ""}]
            $table->string('phone_icon')->default('fa-phone');
            
            // البريد الإلكتروني
            $table->string('email_title')->default('البريد الإلكتروني');
            $table->json('email_addresses')->nullable(); // [{"email": "info@claudsoft.com", "label": ""}]
            $table->string('email_icon')->default('fa-envelope');
            
            // الخريطة
            $table->text('map_embed_url')->nullable();
            $table->boolean('show_map')->default(true);
            
            // وسائل التواصل الاجتماعي
            $table->string('social_title')->default('تابعنا على');
            $table->text('social_subtitle')->nullable();
            $table->json('social_links')->nullable(); // [{"platform": "facebook", "url": "#", "icon": "fa-facebook-f", "label": "فيسبوك", "enabled": true}]
            
            // ساعات العمل
            $table->string('working_hours_title')->default('ساعات العمل');
            $table->json('working_hours')->nullable(); // [{"day": "السبت - الخميس", "time": "9:00 ص - 6:00 م"}, {"day": "الجمعة", "time": "مغلق"}]
            $table->boolean('show_working_hours')->default(true);
            
            // نموذج الاتصال
            $table->string('form_title')->default('أرسل لنا رسالة');
            $table->text('form_subtitle')->nullable();
            $table->boolean('form_enabled')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
