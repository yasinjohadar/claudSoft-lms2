<?php

namespace App\Console\Commands;

use App\Models\EvolutionInstance;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppWebhookEvent;
use App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyService;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Support\WhatsAppRecipientNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

/**
 * يشخّص سبب عدم وصول الرد التلقائي بفحص آخر رسالة واردة حقيقية،
 * وتطبيق شروط البوابة عليها واحداً واحداً وطباعة أول شرط يسقط.
 *
 * سبب وجوده: الفحوص التي تعتمد على الإعدادات وحدها تظهر خضراء بينما
 * تُرفض الرسائل فعلياً لسبب مرتبط بالرسالة نفسها (نوعها، الـ instance، المُرسِل).
 */
class WhatsAppAutoReplyDoctor extends Command
{
    protected $signature = 'whatsapp:autoreply-doctor
                            {--limit=5 : عدد الرسائل الواردة الأخيرة للفحص}';

    protected $description = 'تشخيص سبب عدم إرسال الرد التلقائي على واتساب';

    public function handle(
        WhatsAppSettingsService $settingsService,
        WhatsAppAutoReplyService $autoReplyService
    ): int {
        $settings = $settingsService->getAutoReplySettings();
        $all = $settingsService->getSettings();

        $this->info('=== الإعدادات ===');
        $support = $autoReplyService->resolveSupportInstance($settings);
        $this->table(['المفتاح', 'القيمة'], [
            ['whatsapp_enabled', var_export((bool) ($settings['whatsapp_enabled'] ?? false), true)],
            ['auto_reply', var_export((bool) ($settings['auto_reply'] ?? false), true)],
            ['whatsapp_provider', $settings['whatsapp_provider'] ?? '—'],
            ['auto_reply_use_ai', var_export((bool) ($settings['auto_reply_use_ai'] ?? false), true)],
            ['instance الرد (المستخدم)', $support ?: '— غير محدد —'],
            ['instance موجود؟', ($inst = EvolutionInstance::where('instance_name', $support)->first()) ? 'نعم' : 'لا ✗'],
            ['حالة الاتصال', $inst
                ? ($inst->connection_status === 'open' ? 'open ✔' : $inst->connection_status.' ✗ — أعد ربط الرقم')
                : '—'],
            ['debounce / cooldown', ($settings['auto_reply_debounce_seconds'] ?? '?').'ث / '.($settings['auto_reply_contact_cooldown'] ?? '?').'ث'],
        ]);

        $this->newLine();
        $this->info('=== الطابور ===');
        $this->line('  الاتصال المُعتمَد حالياً: '.config('queue.default'));
        $this->line('  طابور وظائف واتساب: '.config('whatsapp.queue', 'whatsapp')
            .'  ← يجب أن يستمع له العامل');

        // أطوال طوابير Redis (حين يكون هو الاتصال المُعتمَد)
        if (config('queue.default') === 'redis') {
            foreach (array_unique([config('whatsapp.queue', 'whatsapp'), 'default']) as $q) {
                try {
                    $this->line('  redis / '.$q.': '.Queue::connection('redis')->size($q).' وظيفة');
                } catch (\Throwable $e) {
                    $this->error('  تعذّر قراءة طابور redis/'.$q.': '.$e->getMessage());
                }
            }
        }

        // جدول jobs يُفحص دائماً: بعد التحويل إلى redis تبقى وظائف قديمة عالقة
        // في قاعدة البيانات بلا مستهلك، ولا يراها أحد إن لم نعرضها هنا.
        if (Schema::hasTable('jobs') && DB::table('jobs')->exists()) {
            $this->newLine();
            $this->warn('  ⚠ متبقٍ في جدول jobs بقاعدة البيانات (لا يستهلكه أحد إن كان الاتصال redis):');
            $this->line('  الإجمالي: '.DB::table('jobs')->count());
            $this->line('  وظائف فاشلة (24س): '.DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count());

            // التفصيل حسب اسم الطابور: يكشف ما إن كان العامل يستهلك طابوراً دون آخر
            $rows = [];
            foreach (DB::table('jobs')->selectRaw('queue, COUNT(*) c, MIN(available_at) oldest, MAX(attempts) max_attempts')
                ->groupBy('queue')->orderByDesc('c')->get() as $q) {
                $rows[] = [
                    $q->queue,
                    $q->c,
                    date('Y-m-d H:i:s', (int) $q->oldest),
                    $q->max_attempts,
                ];
            }
            if ($rows) {
                $this->newLine();
                $this->table(['الطابور', 'عدد', 'أقدم وظيفة', 'أقصى محاولات'], $rows);

                foreach ($rows as [$queue, $count, $oldest, $attempts]) {
                    if ((int) $attempts > 0) {
                        $this->error('  طابور «'.$queue.'»: وظائف بلغت '.$attempts.' محاولة ولم تُنهَ — '
                            .'العامل يلتقطها ثم يموت قبل إتمامها (خطأ قاتل أو نفاد ذاكرة).');
                    }
                }

                // تفصيل المتراكم حسب صنف الوظيفة: تشغيل العامل سيُنفّذها كلها دفعة
                // واحدة، وقد يكون فيها رسائل بريد قديمة لا يصحّ إرسالها الآن.
                $classes = [];
                foreach (DB::table('jobs')->select('payload')->limit(3000)->get() as $j) {
                    $name = data_get(json_decode($j->payload, true), 'displayName', 'unknown');
                    $classes[$name] = ($classes[$name] ?? 0) + 1;
                }
                arsort($classes);
                if ($classes) {
                    $this->newLine();
                    $this->line('  أصناف الوظائف المتراكمة (الأعلى أولاً):');
                    foreach (array_slice($classes, 0, 8, true) as $name => $count) {
                        $this->line(sprintf('    %-55s %d', $name, $count));
                    }
                    $this->warn('  ⚠ تشغيل العامل سيُنفّذ هذه كلها فوراً — راجعها قبل التشغيل.');
                }

                $whatsappPending = collect($rows)->firstWhere(0, 'whatsapp');
                if ($whatsappPending) {
                    $this->error('  وظائف واتساب منتظرة في قاعدة البيانات: '.$whatsappPending[1]);
                }

                $this->newLine();
                $this->comment('  لتفريغها (تعمل مهما كان QUEUE_CONNECTION لأن الاتصال محدَّد صراحةً):');
                $this->line('    php artisan queue:work database --queue=whatsapp,default --stop-when-empty');
            }
        } else {
            $this->info('  جدول jobs فارغ — لا وظائف عالقة في قاعدة البيانات.');
        }

        $lastEvent = WhatsAppWebhookEvent::orderByDesc('id')->first();
        $unprocessed = WhatsAppWebhookEvent::whereNull('processed_at')->count();
        $this->newLine();
        $this->info('=== أحداث Webhook ===');
        $this->line('  آخر حدث: '.($lastEvent?->created_at?->diffForHumans() ?? '— لا يوجد —'));
        $this->line('  غير معالَجة: '.$unprocessed.($unprocessed > 0 ? '  ← العامل لا يعالجها' : ''));

        // توزيع آخر الأحداث حسب النوع: يميّز «لا يصل شيء» عن «تصل أحداث بلا رسائل»
        $recent = WhatsAppWebhookEvent::orderByDesc('id')->limit(20)->get();
        $types = [];
        foreach ($recent as $ev) {
            $types[(string) data_get($ev->payload, 'event', '?')] = ($types[(string) data_get($ev->payload, 'event', '?')] ?? 0) + 1;
        }
        if ($types) {
            $this->line('  أنواع آخر 20 حدثاً: '.collect($types)->map(fn ($c, $t) => "$t($c)")->implode('، '));
        }

        if ($lastEvent && $lastEvent->created_at?->lt(now()->subMinutes(10))) {
            $this->error('  ⚠ لم يصل أي حدث منذ أكثر من 10 دقائق — Evolution لا يُرسل إلى المنصة.');
            $this->line('     تحقق من: اتصال الـ instance، وتسجيل الـ webhook، ومطابقة مفتاح الترويسة.');
        }

        $messages = WhatsAppMessage::with('contact')
            ->where('direction', WhatsAppMessage::DIRECTION_INBOUND)
            ->orderByDesc('id')
            ->limit((int) $this->option('limit'))
            ->get();

        $this->newLine();
        $this->info('=== آخر الرسائل الواردة ===');

        if ($messages->isEmpty()) {
            $this->error('  لا توجد رسائل واردة محفوظة إطلاقاً.');
            $this->line('  ← الأحداث لا تصل، أو لا تُعالَج، أو المُحلِّل يتجاهلها (رسائل مجموعات / غير نصية).');

            return self::FAILURE;
        }

        foreach ($messages as $msg) {
            $this->newLine();
            $this->line('── رسالة #'.$msg->id.'  ('.$msg->created_at?->format('Y-m-d H:i:s').')');
            $this->line('   من: '.($msg->contact?->wa_id ?? '—').'   النوع: '.$msg->type);
            $this->line('   النص: '.mb_substr((string) $msg->body, 0, 80));

            $reason = $this->gateReason($msg, $settings, $autoReplyService);

            if ($reason === null) {
                $this->info('   ✔ تجتاز البوابة — كان يجب أن يُرسَل رد.');

                $reply = WhatsAppMessage::where('contact_id', $msg->contact_id)
                    ->where('direction', WhatsAppMessage::DIRECTION_OUTBOUND)
                    ->where('id', '>', $msg->id)
                    ->orderBy('id')
                    ->first();

                if ($reply) {
                    $line = '   ← رد صادر #'.$reply->id.' حالته: '.$reply->status;
                    $reply->status === 'failed'
                        ? $this->error($line.'  السبب: '.json_encode($reply->error, JSON_UNESCAPED_UNICODE))
                        : $this->info($line);
                } else {
                    $this->error('   ← لا يوجد رد صادر: البوابة تسمح لكن المعالجة لم تكتمل.');
                    $this->line('      افحص storage/logs/whatsapp-*.log بحثاً عن AutoReply لهذه اللحظة.');
                }
            } else {
                $this->error('   ✗ مرفوضة: '.$reason);
            }
        }

        $this->newLine();
        $this->comment('تلميح: بعد كل نشر نفّذ php artisan queue:restart — عمال supervisor يبقون على الكود القديم في الذاكرة.');

        return self::SUCCESS;
    }

    /**
     * يعيد أول سبب رفض، أو null إن اجتازت الرسالة كل الشروط.
     * يطابق ترتيب الشروط في WhatsAppAutoReplyService::passesInboundGate().
     */
    protected function gateReason(WhatsAppMessage $msg, array $settings, WhatsAppAutoReplyService $svc): ?string
    {
        if ($msg->type !== 'text') {
            return 'النوع ليس نصاً ('.$msg->type.') — الصور والصوت والملفات لا يُرد عليها.';
        }

        if (trim((string) $msg->body) === '') {
            return 'نص الرسالة فارغ.';
        }

        if (! ($settings['whatsapp_enabled'] ?? false)) {
            return 'WhatsApp معطَّل في الإعدادات.';
        }

        if (! ($settings['auto_reply'] ?? false)) {
            return 'الرد التلقائي معطَّل في الإعدادات.';
        }

        if (($settings['whatsapp_provider'] ?? '') !== 'evolution') {
            return 'المزود ليس Evolution (الحالي: '.($settings['whatsapp_provider'] ?? '—').').';
        }

        $support = $svc->resolveSupportInstance($settings);
        if ($support === '') {
            return 'لم يُحدَّد instance للدعم.';
        }

        $inbound = $svc->resolveInboundInstance($msg);
        if ($inbound !== '' && ! $svc->inboundBelongsToSupportInstance($msg, $support, $inbound)) {
            return 'اختلاف instance — وصلت عبر «'.$inbound.'» بينما المضبوط «'.$support.'».';
        }

        $replyJid = $svc->resolveReplyJid($msg) ?: ($msg->contact?->wa_id ?? '');
        if (! $msg->contact || $replyJid === '' || ! WhatsAppRecipientNormalizer::isReplyableRecipient($replyJid)) {
            return 'المُرسِل غير قابل للرد عليه (jid: '.($replyJid ?: '—').').';
        }

        if (Cache::has('auto_reply_cooldown:'.$msg->contact_id)) {
            return 'مهلة التهدئة ما زالت فعّالة لهذه الجهة (cooldown).';
        }

        return null;
    }
}
