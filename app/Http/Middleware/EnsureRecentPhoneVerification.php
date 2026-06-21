<?php

namespace App\Http\Middleware;

use App\Enums\OtpPurpose;
use App\Services\Auth\PhoneOtpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecentPhoneVerification
{
    public function __construct(
        private PhoneOtpService $otpService
    ) {}

    public function handle(Request $request, Closure $next, string $purpose = 'sensitive_action'): Response
    {
        $otpPurpose = OtpPurpose::tryFrom($purpose) ?? OtpPurpose::SensitiveAction;
        $phone = $request->user()?->full_phone
            ?? trim(($request->user()?->country_code ?? '').($request->user()?->phone ?? ''));

        if ($phone === '') {
            return redirect()->back()->with('error', 'لا يوجد رقم هاتف مسجّل للتحقق.');
        }

        try {
            $normalized = $this->otpService->normalizePhone($phone);
        } catch (\InvalidArgumentException) {
            return redirect()->back()->with('error', 'رقم الهاتف غير صالح.');
        }

        if (! $this->otpService->hasRecentVerification($normalized, $otpPurpose)) {
            session(['phone_otp_after_verify_url' => $request->fullUrl()]);

            return redirect()->route('phone-otp.verify', [
                'purpose' => $otpPurpose->value,
                'phone' => $normalized,
            ])->with('warning', 'يجب التحقق من هاتفك لإتمام هذه العملية.');
        }

        return $next($request);
    }
}
