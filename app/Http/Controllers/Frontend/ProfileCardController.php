<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentProfileCardAccessService;
use App\Services\Student\StudentProfileCardQrService;
use Illuminate\View\View;

class ProfileCardController extends Controller
{
    public function show(
        string $slug,
        StudentProfileCardAccessService $accessService,
        StudentProfileCardQrService $qrService
    ): View {
        $card = $accessService->resolvePublicCard($slug);

        if (! $card) {
            abort(404);
        }

        $user = $card->user;
        $qrUrl = $card->qr_enabled ? $qrService->qrUrl($card) : null;

        return view('frontend2.pages.profile-card.show', compact('card', 'user', 'qrUrl'));
    }
}
