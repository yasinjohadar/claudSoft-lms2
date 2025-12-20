<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::where('user_id', auth()->id())
            ->with(['course', 'template'])
            ->latest()
            ->paginate(10);

        return view('student.certificates.index', compact('certificates'));
    }

    public function show(Certificate $certificate)
    {
        // التأكد أن الشهادة تخص الطالب المسجل دخوله
        if ($certificate->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بعرض هذه الشهادة');
        }

        $certificate->load(['course', 'template']);

        return view('student.certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        // التأكد أن الشهادة تخص الطالب المسجل دخوله
        if ($certificate->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتحميل هذه الشهادة');
        }

        if (!$certificate->canBeDownloaded()) {
            return back()->with('error', 'لا يمكن تحميل هذه الشهادة');
        }

        $certificate->incrementDownloadCount();

        return response()->download(
            storage_path('app/public/' . $certificate->pdf_path),
            $certificate->certificate_number . '.pdf'
        );
    }
}
