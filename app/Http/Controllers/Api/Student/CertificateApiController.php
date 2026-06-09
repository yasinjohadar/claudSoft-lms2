<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificateApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);

        $paginator = Certificate::query()
            ->where('user_id', $request->user()->id)
            ->with(['course', 'template'])
            ->latest()
            ->paginate($perPage);

        $items = collect($paginator->items())->map(fn (Certificate $c) => $this->serialize($c))->values();

        return response()->json([
            'success' => true,
            'data' => [
                'certificates' => $items,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, Certificate $certificate): JsonResponse
    {
        $this->authorizeCertificate($certificate, $request->user()->id);
        $certificate->load(['course', 'template']);

        return response()->json([
            'success' => true,
            'data' => $this->serialize($certificate, true),
        ]);
    }

    public function download(Request $request, Certificate $certificate): BinaryFileResponse|JsonResponse
    {
        $this->authorizeCertificate($certificate, $request->user()->id);

        if (!$certificate->canBeDownloaded()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تحميل هذه الشهادة',
            ], 422);
        }

        $certificate->incrementDownloadCount();

        return response()->download(
            storage_path('app/public/' . $certificate->pdf_path),
            $certificate->certificate_number . '.pdf'
        );
    }

    private function authorizeCertificate(Certificate $certificate, int $userId): void
    {
        if ((int) $certificate->user_id !== $userId) {
            abort(403, 'غير مصرح لك بعرض هذه الشهادة');
        }
    }

    private function serialize(Certificate $certificate, bool $detailed = false): array
    {
        $data = [
            'id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'course_id' => $certificate->course_id,
            'course_name' => $certificate->course_name ?: $certificate->course?->title,
            'student_name' => $certificate->student_name,
            'issue_date' => optional($certificate->issue_date)->toDateString(),
            'completion_date' => optional($certificate->completion_date)->toDateString(),
            'completion_percentage' => (float) $certificate->completion_percentage,
            'status' => $certificate->status,
            'verification_code' => $certificate->verification_code,
            'verification_url' => $certificate->verification_url,
            'pdf_url' => $certificate->pdf_url,
            'can_download' => $certificate->canBeDownloaded(),
            'download_count' => (int) $certificate->download_count,
        ];

        if ($detailed) {
            $data['expiry_date'] = optional($certificate->expiry_date)->toDateString();
            $data['attendance_percentage'] = (float) $certificate->attendance_percentage;
            $data['final_exam_score'] = (float) $certificate->final_exam_score;
            $data['course_hours'] = $certificate->course_hours;
            $data['qr_code_url'] = $certificate->qr_code_url;
            $data['metadata'] = $certificate->metadata;
        }

        return $data;
    }
}
