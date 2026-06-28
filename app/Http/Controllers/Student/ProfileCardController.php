<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileCardRequest;
use App\Services\Student\StudentAccountTierService;
use App\Services\Student\StudentProfileCardAccessService;
use App\Services\Student\StudentProfileCardQrService;
use App\Services\Student\StudentProfileCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileCardController extends Controller
{
    public function edit(
        StudentProfileCardAccessService $accessService,
        StudentProfileCardService $cardService,
        StudentAccountTierService $tierService,
        StudentProfileCardQrService $qrService
    ): View {
        $user = auth()->user();
        $canUse = $accessService->canUseFeature($user);
        $accountTier = $tierService->resolve($user);

        if (! $canUse) {
            return view('student.pages.profile-card.upgrade', compact('accountTier'));
        }

        $card = $cardService->getOrCreateForUser($user);
        $socialPlatforms = config('profile-card.social_platforms', []);
        $themes = config('profile-card.themes', []);
        $publicUrl = $card->public_url;
        $qrUrl = $qrService->qrUrl($card);

        return view('student.pages.profile-card.edit', compact(
            'card',
            'user',
            'socialPlatforms',
            'themes',
            'publicUrl',
            'qrUrl',
            'accountTier'
        ));
    }

    public function update(
        UpdateProfileCardRequest $request,
        StudentProfileCardAccessService $accessService,
        StudentProfileCardService $cardService
    ): RedirectResponse {
        $user = auth()->user();

        if (! $accessService->canUseFeature($user)) {
            return redirect()->route('student.profile-card.edit')
                ->with('error', 'ميزة البطاقة التعريفية غير متاحة لحسابك حالياً.');
        }

        $validated = $request->validated();
        $validated['is_public'] = $request->boolean('is_public');
        $validated['qr_enabled'] = $request->boolean('qr_enabled');

        $cardService->updateForUser($user, $validated);

        return redirect()->route('student.profile-card.edit')
            ->with('success', 'تم حفظ بطاقتك التعريفية بنجاح.');
    }

    public function togglePublic(
        Request $request,
        StudentProfileCardAccessService $accessService,
        StudentProfileCardService $cardService
    ): JsonResponse {
        $user = auth()->user();

        if (! $accessService->canUseFeature($user)) {
            return response()->json(['success' => false, 'message' => 'الميزة غير متاحة'], 403);
        }

        $isPublic = $request->boolean('is_public');
        $card = $cardService->togglePublic($user, $isPublic);

        return response()->json([
            'success' => true,
            'is_public' => $card->is_public,
            'message' => $card->is_public ? 'البطاقة ظاهرة للعامة' : 'البطاقة مخفية عن العامة',
        ]);
    }

    public function regenerateQr(
        StudentProfileCardAccessService $accessService,
        StudentProfileCardService $cardService,
        StudentProfileCardQrService $qrService
    ): JsonResponse {
        $user = auth()->user();

        if (! $accessService->canUseFeature($user)) {
            return response()->json(['success' => false, 'message' => 'الميزة غير متاحة'], 403);
        }

        $card = $cardService->getOrCreateForUser($user);
        $qrService->generate($card);

        return response()->json([
            'success' => true,
            'qr_url' => $qrService->qrUrl($card->fresh()),
            'message' => 'تم إعادة توليد رمز QR',
        ]);
    }

    public function preview(
        StudentProfileCardAccessService $accessService,
        StudentProfileCardService $cardService,
        StudentProfileCardQrService $qrService
    ): View {
        $user = auth()->user();

        if (! $accessService->canUseFeature($user)) {
            abort(403);
        }

        $card = $cardService->getOrCreateForUser($user);
        $qrUrl = $card->qr_enabled ? $qrService->qrUrl($card) : null;

        return view('frontend2.pages.profile-card.show', [
            'card' => $card,
            'user' => $user,
            'qrUrl' => $qrUrl,
            'isPreview' => true,
        ]);
    }
}
