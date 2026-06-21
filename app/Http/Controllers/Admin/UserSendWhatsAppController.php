<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvolutionInstance;
use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsApp\UserSendWhatsAppService;
use App\Support\WhatsAppSendErrorMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSendWhatsAppController extends Controller
{
    public function __construct(
        private UserSendWhatsAppService $sender
    ) {
        $this->middleware('auth');
        $this->middleware('permission:user-edit');
    }

    public function preview(Request $request, User $user): JsonResponse
    {
        $validated = $this->validateRequest($request, $user);

        $template = WhatsAppMessageTemplate::active()
            ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
            ->where('id', $validated['whatsapp_template_id'])
            ->firstOrFail();

        $body = $this->sender->renderTemplateForUser($template, $user);

        return response()->json([
            'success' => true,
            'body' => $body,
        ]);
    }

    public function send(Request $request, User $user): JsonResponse
    {
        $validated = $this->validateRequest($request, $user);

        $template = WhatsAppMessageTemplate::active()
            ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
            ->where('id', $validated['whatsapp_template_id'])
            ->firstOrFail();

        try {
            $this->sender->sendTemplateToUser(
                $user,
                $template,
                $validated['evolution_instance_name'] ?? null
            );

            $phone = $user->full_phone
                ?: trim(($user->country_code ?? '').($user->phone ?? ''));

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالة الواتساب بنجاح إلى '.$phone,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الإرسال: '.WhatsAppSendErrorMessage::fromThrowable($e),
            ], 500);
        }
    }

    /**
     * @return array{whatsapp_template_id: int, evolution_instance_name?: string|null}
     */
    private function validateRequest(Request $request, User $user): array
    {
        $phone = trim((string) ($user->full_phone ?? ''));
        if ($phone === '') {
            $phone = trim(($user->country_code ?? '').($user->phone ?? ''));
        }

        if ($phone === '') {
            abort(response()->json([
                'success' => false,
                'message' => 'لا يوجد رقم واتساب لهذا المستخدم.',
            ], 422));
        }

        $validated = $request->validate([
            'whatsapp_template_id' => 'required|exists:whatsapp_message_templates,id',
            'evolution_instance_name' => 'nullable|string|max:255',
        ]);

        if (! empty($validated['evolution_instance_name'])) {
            $exists = EvolutionInstance::where('instance_name', $validated['evolution_instance_name'])->exists();
            if (! $exists) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Instance Evolution المحدد غير موجود.',
                ], 422));
            }
        }

        return $validated;
    }
}
