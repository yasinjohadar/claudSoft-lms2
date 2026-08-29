<?php

use App\Jobs\ProcessWhatsAppAutoReplyJob;
use App\Jobs\ProcessWhatsAppWebhookEventJob;
use Illuminate\Http\Request;

/*
| اختبارات انحدار للأعطال التي كانت تمنع وصول أي رسالة واتساب:
| 1) قيد الراوت كان يرفض أسماء instances التي تحوي مسافات فتُرد الأحداث بـ 404 صامت
| 2) خاصية $queue المُعرَّفة بنوع كانت تجعل وظائف الطابور غير قابلة للتحميل أصلاً (Fatal)
*/

uses(Tests\TestCase::class);

test('webhook route accepts instance names containing spaces', function () {
    $route = app('router')->getRoutes()
        ->match(Request::create('/api/webhooks/evolution/whatsapp%20ClaudSoft', 'POST'));

    expect($route->parameter('instance'))->toBe('whatsapp ClaudSoft');
});

test('webhook route accepts simple and bare forms', function () {
    $routes = app('router')->getRoutes();

    expect($routes->match(Request::create('/api/webhooks/evolution/ClaudSoftServices', 'POST'))->parameter('instance'))
        ->toBe('ClaudSoftServices');

    // الشكل المجرَّد (بلا اسم في المسار) مدعوم — يُقرأ الاسم من جسم الطلب
    expect($routes->match(Request::create('/api/webhooks/evolution', 'POST'))->parameter('instance'))
        ->toBeNull();
});

test('whatsapp queue jobs are loadable and configured for the whatsapp queue', function () {
    // كانت `public string $queue` تتعارض مع تريت Queueable فيسقط الصنف بخطأ قاتل،
    // أي أن استدعاء الـ webhook كان ينهار قبل جدولة أي وظيفة على الإطلاق.
    expect(class_exists(ProcessWhatsAppWebhookEventJob::class))->toBeTrue();
    expect(class_exists(ProcessWhatsAppAutoReplyJob::class))->toBeTrue();

    $job = new ProcessWhatsAppAutoReplyJob(123);

    expect($job->queue)->toBe('whatsapp')
        ->and($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(300);
});

test('auto reply job prevents overlapping runs for the same contact', function () {
    $middleware = (new ProcessWhatsAppAutoReplyJob(77))->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(Illuminate\Queue\Middleware\WithoutOverlapping::class);
});
