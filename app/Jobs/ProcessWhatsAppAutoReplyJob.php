<?php

namespace App\Jobs;

use App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppAutoReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 3 لا 10: مع الذاكرة المؤقتة القابلة للاستئناف صارت كل محاولة مفيدة فعلاً،
     * وعشر محاولات على خطأ دائم تعني تأخيراً طويلاً بلا فائدة.
     */
    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    /**
     * 300 لا 120: أسوأ حالة = 3 أجزاء × (كتابة 15ث + فاصل إرسال) وقد تتجاوز 120ث
     * فيُقتل الرد في منتصفه. التأخير الابتدائي انتقل إلى جدولة الوظيفة.
     */
    public int $timeout = 300;

    public function __construct(
        public int $contactId,
    ) {
        // الطابور يُضبط هنا لا بخاصية `public string $queue`: تريت Queueable
        // يعرّف $queue بلا نوع وبقيمة ابتدائية null، وإعادة تعريفها في الصنف بنوع
        // أو بقيمة مختلفة تجعل تركيب الصنف فاشلاً (Fatal) في PHP 8.
        $this->onQueue(config('whatsapp.queue', 'whatsapp'));
    }

    /**
     * يمنع تشغيل وظيفتَي رد لنفس جهة الاتصال معاً — وهو ما قد يُنتج رداً مزدوجاً
     * حين تصل عدة رسائل متتابعة فتُجدول كلٌّ منها وظيفة.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping((string) $this->contactId))
                ->releaseAfter(10)
                ->expireAfter(360),
        ];
    }

    public function handle(WhatsAppAutoReplyService $autoReplyService): void
    {
        if ($autoReplyService->shouldDeferAutoReply($this->contactId)) {
            $wait = $autoReplyService->secondsUntilAutoReplyReady($this->contactId);
            Log::channel('whatsapp')->info('AutoReply: deferring job for debounce', [
                'contact_id' => $this->contactId,
                'wait_seconds' => $wait,
            ]);
            $this->release(max(1, $wait));

            return;
        }

        $autoReplyService->processContact($this->contactId);
    }

    /**
     * بعد استنفاد المحاولات تُمسح الذاكرة المؤقتة، وإلا بقيت رسائل قديمة عالقة
     * فيها فأفسدت أول رد لاحق لنفس جهة الاتصال.
     */
    public function failed(\Throwable $e): void
    {
        Log::channel('whatsapp')->error('AutoReply: job failed permanently', [
            'contact_id' => $this->contactId,
            'error' => $e->getMessage(),
        ]);

        app(WhatsAppAutoReplyService::class)->abandonAutoReply($this->contactId);
    }
}
