<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramLinkService;
use App\Services\Telegram\TelegramSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TelegramLinkController extends Controller
{
    public function __construct(
        private TelegramLinkService $linkService,
        private TelegramSettingsService $settingsService,
    ) {}

    public function show(): View
    {
        $user = auth()->user();
        $settings = $this->settingsService->getSettings();
        $linkUrl = $this->linkService->botStartLink($user);

        return view('student.telegram-link', compact('user', 'settings', 'linkUrl'));
    }

    public function unlink(): RedirectResponse
    {
        $this->linkService->unlink(auth()->user());

        return back()->with('success', 'تم فك ربط Telegram.');
    }
}
