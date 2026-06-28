<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentProfileCardSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.pages.student-profile-cards.settings', [
            'silverEnabled' => SiteSetting::isProfileCardEnabledForSilver(),
            'goldEnabled' => SiteSetting::isProfileCardEnabledForGold(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $silverEnabled = $request->has('profile_card_enabled_silver')
            && $request->input('profile_card_enabled_silver') == '1';
        $goldEnabled = $request->has('profile_card_enabled_gold')
            && $request->input('profile_card_enabled_gold') == '1';

        SiteSetting::setValue(
            'profile_card_enabled_silver',
            $silverEnabled,
            'تفعيل بطاقة الطالب التعريفية للحسابات الفضية'
        );

        SiteSetting::setValue(
            'profile_card_enabled_gold',
            $goldEnabled,
            'تفعيل بطاقة الطالب التعريفية للحسابات الذهبية'
        );

        return redirect()
            ->route('admin.student-profile-cards.settings')
            ->with('success', 'تم تحديث إعدادات البطاقة التعريفية بنجاح.');
    }
}
