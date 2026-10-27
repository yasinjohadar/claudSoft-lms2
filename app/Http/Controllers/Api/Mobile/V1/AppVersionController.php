<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Models\MobileAppRelease;
use App\Services\MobileApp\MobileAppReleaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * فحص تحديثات التطبيق (عام: يعمل قبل تسجيل الدخول أيضاً حتى يُفرض التحديث الإجباري).
 */
class AppVersionController extends BaseMobileController
{
    public function __construct(protected MobileAppReleaseService $releases) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', Rule::in(array_keys(MobileAppRelease::PLATFORMS))],
            'build' => ['required', 'integer', 'min:0'],
        ]);

        return $this->success($this->releases->check($validated['platform'], (int) $validated['build']));
    }
}
