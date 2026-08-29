<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * الرسائل: مجموعة فرعية من إشعارات الطالب علّمها الأدمن صراحةً كـ"رسالة" عند الإرسال.
     * تستخدم نفس نقاط النهاية الموجودة أصلاً لتحديد كمقروء/حذف (gamification.notifications.*)
     * لأنها تعمل على أي إشعار مملوك للطالب بصرف النظر عن نوعه.
     */
    public function index(Request $request)
    {
        $messages = $request->user()
            ->gamificationNotifications()
            ->messages()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('student.gamification.messages.index', compact('messages'));
    }
}
