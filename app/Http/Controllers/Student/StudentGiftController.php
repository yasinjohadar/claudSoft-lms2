<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentGiftRecipient;
use App\Services\StudentGifts\StudentGiftDeliveryService;
use Illuminate\Http\Request;

class StudentGiftController extends Controller
{
    public function __construct(
        protected StudentGiftDeliveryService $deliveryService
    ) {}

    public function index(Request $request)
    {
        $baseQuery = StudentGiftRecipient::query()
            ->where('student_id', auth()->id())
            ->whereHas('gift', fn ($q) => $q->where('status', 'granted'));

        $recipients = (clone $baseQuery)
            ->with('gift')
            ->latest('granted_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'downloaded' => (clone $baseQuery)->whereNotNull('downloaded_at')->count(),
            'previewed' => (clone $baseQuery)->whereNotNull('previewed_at')->count(),
            'filtered' => $recipients->total(),
        ];

        return view('student.pages.gifts.index', compact('recipients', 'stats'));
    }

    public function preview(StudentGiftRecipient $recipient)
    {
        return $this->deliveryService->preview(auth()->user(), $recipient);
    }

    public function download(StudentGiftRecipient $recipient)
    {
        return $this->deliveryService->download(auth()->user(), $recipient);
    }
}
