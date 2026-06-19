<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\BulkEmail\BulkEmailSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSendEmailController extends Controller
{
    public function __construct(
        private BulkEmailSender $sender
    ) {
        $this->middleware('auth');
        $this->middleware('permission:user-edit');
    }

    public function preview(Request $request, User $user): JsonResponse
    {
        $validated = $this->validateRequest($request, $user);

        $template = EmailTemplate::where('id', $validated['email_template_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $rendered = $this->sender->renderTemplateForUser($template, $user);

        return response()->json([
            'success' => true,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ]);
    }

    public function send(Request $request, User $user): JsonResponse
    {
        $validated = $this->validateRequest($request, $user);

        $template = EmailTemplate::where('id', $validated['email_template_id'])
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $this->sender->sendTemplateToUser(
                $user,
                $template,
                $validated['email_setting_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال البريد بنجاح إلى '.$user->email,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الإرسال: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * @return array{email_template_id: int, email_setting_id?: int|null}
     */
    private function validateRequest(Request $request, User $user): array
    {
        if (trim((string) ($user->email ?? '')) === '') {
            abort(response()->json([
                'success' => false,
                'message' => 'لا يوجد بريد إلكتروني لهذا المستخدم.',
            ], 422));
        }

        return $request->validate([
            'email_template_id' => 'required|exists:email_templates,id',
            'email_setting_id' => 'nullable|exists:email_settings,id',
        ]);
    }
}
