<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampEnrollment;
use App\Models\StudentProfileCard;
use App\Services\Student\StudentAccountTierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentProfileCardController extends Controller
{
    public function index(Request $request, StudentAccountTierService $tierService): View
    {
        $query = StudentProfileCard::query()->with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('is_public')) {
            $query->where('is_public', $request->input('is_public') === '1');
        }

        if ($request->filled('admin_enabled')) {
            $query->where('admin_enabled', $request->input('admin_enabled') === '1');
        }

        if ($request->filled('tier')) {
            $goldStudentIds = CampEnrollment::query()
                ->approved()
                ->whereHas('camp')
                ->pluck('student_id')
                ->unique();

            if ($request->input('tier') === 'gold') {
                $query->whereIn('user_id', $goldStudentIds);
            } elseif ($request->input('tier') === 'silver') {
                $query->whereNotIn('user_id', $goldStudentIds);
            }
        }

        $cards = $query->paginate(20)->withQueryString();

        $tierByUserId = [];
        foreach ($cards as $card) {
            if ($card->user) {
                $tierByUserId[$card->user_id] = $tierService->resolve($card->user);
            }
        }

        $stats = [
            'total' => StudentProfileCard::count(),
            'public' => StudentProfileCard::where('is_public', true)->count(),
            'active' => StudentProfileCard::where('admin_enabled', true)->count(),
            'inactive' => StudentProfileCard::where('admin_enabled', false)->count(),
        ];

        return view('admin.pages.student-profile-cards.index', compact('cards', 'tierByUserId', 'stats'));
    }

    public function toggleAdminEnabled(StudentProfileCard $studentProfileCard): JsonResponse
    {
        try {
            $studentProfileCard->update([
                'admin_enabled' => ! $studentProfileCard->admin_enabled,
            ]);
            $studentProfileCard->refresh();

            return response()->json([
                'success' => true,
                'admin_enabled' => $studentProfileCard->admin_enabled,
                'message' => $studentProfileCard->admin_enabled
                    ? 'تم تفعيل البطاقة'
                    : 'تم إيقاف البطاقة من الأدمن',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 500);
        }
    }
}
