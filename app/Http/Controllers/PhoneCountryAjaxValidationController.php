<?php

namespace App\Http\Controllers;

use App\Support\UserPhoneCountryValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PhoneCountryAjaxValidationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_code' => 'nullable|string|max:16',
            'phone' => 'nullable|string|max:32',
        ]);

        $cc = trim((string) ($data['country_code'] ?? ''));
        $ph = trim((string) ($data['phone'] ?? ''));

        try {
            $message = UserPhoneCountryValidator::validatePair(
                $cc !== '' ? $cc : null,
                $ph
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'valid' => false,
                'message' => 'تعذر التحقق من الرقم الآن. حاول مرة أخرى بعد قليل، أو تأكد من إدخال الرقم بصيغة صحيحة لبلدك.',
            ]);
        }

        if ($message !== null) {
            return response()->json([
                'valid' => false,
                'message' => $message,
            ]);
        }

        $okMsg = ($cc === '' && $ph === '') ? '' : 'الرقم يطابق رمز الدولة المختار.';

        return response()->json([
            'valid' => true,
            'message' => $okMsg,
        ]);
    }
}
