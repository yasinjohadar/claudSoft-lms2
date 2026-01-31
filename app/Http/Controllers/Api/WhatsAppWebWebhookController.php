<?php

namespace App\Http\Controllers\Api;

use App\Events\WhatsAppMessageReceived;
use App\Http\Controllers\Controller;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller لاستقبال الرسائل الواردة من خدمة الواتساب ويب (Node.js / whatsapp-web.js)
 */
class WhatsAppWebWebhookController extends Controller
{
    public function __construct(
        private WhatsAppSettingsService $settingsService
    ) {}

    /**
     * استقبال رسالة واردة من خدمة الواتساب ويب
     * 
     * Expected payload from Node.js service:
     * {
     *   "event": "message",
     *   "from": "966501234567@c.us",
     *   "body": "مرحبا",
     *   "type": "chat",
     *   "timestamp": 1706600000,
     *   "messageId": "true_966501234567@c.us_ABC123",
     *   "notifyName": "اسم المرسل",
     *   "isGroup": false
     * }
     */
    public function handleIncoming(Request $request)
    {
        // التحقق من التوكن
        if (!$this->verifyToken($request)) {
            Log::channel('whatsapp')->warning('WhatsApp Web webhook: invalid token', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? 'message';

        Log::channel('whatsapp')->info('WhatsApp Web webhook received', [
            'event' => $event,
            'from' => $payload['from'] ?? 'unknown',
            'type' => $payload['type'] ?? 'unknown',
        ]);

        // معالجة حسب نوع الحدث
        if ($event === 'message' || $event === 'chat') {
            return $this->processIncomingMessage($payload);
        }

        if ($event === 'status' || $event === 'ack') {
            return $this->processStatusUpdate($payload);
        }

        // أحداث أخرى (مثل connection, disconnection) - تسجيل فقط
        Log::channel('whatsapp')->info('WhatsApp Web webhook: unhandled event', [
            'event' => $event,
            'payload' => $payload,
        ]);

        return response()->json(['success' => true, 'message' => 'Event logged']);
    }

    /**
     * معالجة رسالة واردة
     */
    protected function processIncomingMessage(array $payload): \Illuminate\Http\JsonResponse
    {
        try {
            // استخراج البيانات من الـ payload
            $from = $this->normalizePhoneNumber($payload['from'] ?? '');
            $body = $payload['body'] ?? '';
            $type = $this->mapMessageType($payload['type'] ?? 'chat');
            $messageId = $payload['messageId'] ?? $payload['id'] ?? null;
            $notifyName = $payload['notifyName'] ?? $payload['pushName'] ?? null;
            $isGroup = $payload['isGroup'] ?? false;

            // تجاهل رسائل المجموعات إن لم تكن مدعومة
            if ($isGroup) {
                Log::channel('whatsapp')->info('WhatsApp Web: group message ignored', [
                    'from' => $from,
                ]);
                return response()->json(['success' => true, 'message' => 'Group message ignored']);
            }

            if (empty($from)) {
                return response()->json(['error' => 'Missing "from" field'], 400);
            }

            // إنشاء أو تحديث جهة الاتصال
            $contact = WhatsAppContact::findOrCreateByWaId($from);
            if ($notifyName && empty($contact->name)) {
                $contact->update(['name' => $notifyName]);
            }
            $contact->updateLastSeen();

            // إنشاء سجل الرسالة
            $message = WhatsAppMessage::create([
                'direction' => WhatsAppMessage::DIRECTION_INBOUND,
                'contact_id' => $contact->id,
                'meta_message_id' => $messageId,
                'type' => $type,
                'body' => $body,
                'status' => WhatsAppMessage::STATUS_DELIVERED,
                'payload' => $payload,
            ]);

            // إطلاق الحدث (يستمع إليه AutoReplyWhatsAppListener)
            event(new WhatsAppMessageReceived($message));

            Log::channel('whatsapp')->info('WhatsApp Web: inbound message processed', [
                'message_id' => $message->id,
                'from' => $from,
                'type' => $type,
            ]);

            return response()->json([
                'success' => true,
                'message_id' => $message->id,
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('WhatsApp Web: error processing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * معالجة تحديث حالة الرسالة
     */
    protected function processStatusUpdate(array $payload): \Illuminate\Http\JsonResponse
    {
        $messageId = $payload['messageId'] ?? $payload['id'] ?? null;
        $status = $payload['status'] ?? $payload['ack'] ?? null;

        if (!$messageId) {
            return response()->json(['success' => true, 'message' => 'No message ID']);
        }

        $message = WhatsAppMessage::byMetaMessageId($messageId)->first();
        if (!$message) {
            return response()->json(['success' => true, 'message' => 'Message not found']);
        }

        // تحويل حالة whatsapp-web.js إلى حالة النظام
        $statusMap = [
            'pending' => WhatsAppMessage::STATUS_QUEUED,
            'sent' => WhatsAppMessage::STATUS_SENT,
            'received' => WhatsAppMessage::STATUS_DELIVERED,
            'read' => WhatsAppMessage::STATUS_READ,
            'played' => WhatsAppMessage::STATUS_READ,
            // ack values from whatsapp-web.js
            '0' => WhatsAppMessage::STATUS_QUEUED,  // ACK_ERROR
            '1' => WhatsAppMessage::STATUS_SENT,    // ACK_PENDING
            '2' => WhatsAppMessage::STATUS_SENT,    // ACK_SERVER
            '3' => WhatsAppMessage::STATUS_DELIVERED, // ACK_DEVICE
            '4' => WhatsAppMessage::STATUS_READ,    // ACK_READ
            '5' => WhatsAppMessage::STATUS_READ,    // ACK_PLAYED
        ];

        $newStatus = $statusMap[(string)$status] ?? $message->status;
        $message->update(['status' => $newStatus]);

        Log::channel('whatsapp')->info('WhatsApp Web: status updated', [
            'message_id' => $message->id,
            'status' => $newStatus,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * التحقق من صحة التوكن
     */
    protected function verifyToken(Request $request): bool
    {
        $settings = $this->settingsService->getSettings();
        $expectedToken = $settings['whatsapp_web_api_token'] ?? '';

        // إذا لم يكن هناك توكن مضبوط، السماح (للتطوير فقط)
        if (empty($expectedToken)) {
            return true;
        }

        // التحقق من Authorization header
        $authHeader = $request->header('Authorization', '');
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            if ($token === $expectedToken) {
                return true;
            }
        }

        // التحقق من query parameter أو body
        $token = $request->input('api_token') ?? $request->query('api_token');
        if ($token === $expectedToken) {
            return true;
        }

        return false;
    }

    /**
     * تطبيع رقم الهاتف (إزالة @c.us وما شابه)
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // إزالة @c.us أو @s.whatsapp.net
        $phone = preg_replace('/@[a-z.]+$/', '', $phone);
        
        // إزالة أي أحرف غير رقمية
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return $phone;
    }

    /**
     * تحويل نوع الرسالة من whatsapp-web.js إلى نوع النظام
     */
    protected function mapMessageType(string $type): string
    {
        $typeMap = [
            'chat' => WhatsAppMessage::TYPE_TEXT,
            'text' => WhatsAppMessage::TYPE_TEXT,
            'image' => WhatsAppMessage::TYPE_IMAGE,
            'video' => WhatsAppMessage::TYPE_VIDEO,
            'audio' => WhatsAppMessage::TYPE_AUDIO,
            'ptt' => WhatsAppMessage::TYPE_AUDIO, // voice note
            'document' => WhatsAppMessage::TYPE_DOCUMENT,
            'sticker' => 'sticker',
            'location' => 'location',
            'contact' => 'contact',
        ];

        return $typeMap[$type] ?? WhatsAppMessage::TYPE_TEXT;
    }
}
