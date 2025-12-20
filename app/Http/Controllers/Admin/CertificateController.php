<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseEnrollment;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
        $this->middleware('permission:certificates.view', ['only' => ['index', 'show']]);
        $this->middleware('permission:certificates.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:certificates.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:certificates.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Certificate::with(['student', 'course', 'template']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%")
                  ->orWhere('course_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $certificates = $query->latest()->paginate(20);
        $statistics = $this->certificateService->getStatistics();
        $courses = Course::orderBy('title')->get();

        return view('admin.pages.certificates.index', compact('certificates', 'statistics', 'courses'));
    }

    public function create()
    {
        $courses = Course::with(['enrollments.student'])->orderBy('title')->get();
        $templates = CertificateTemplate::active()->ordered()->get();
        // Get all students using Spatie's role method
        $students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->orderBy('name')->get();

        return view('admin.pages.certificates.create', compact('courses', 'templates', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'template_id' => 'required|exists:certificate_templates,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $course = Course::findOrFail($request->course_id);
            $template = CertificateTemplate::findOrFail($request->template_id);

            $enrollment = CourseEnrollment::where('student_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            $certificate = $this->certificateService->issueCertificate(
                $user,
                $course,
                $template,
                $enrollment,
                auth()->id()
            );

            return redirect()->route('admin.certificates.show', $certificate->id)
                ->with('success', 'تم إصدار الشهادة بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['student', 'course', 'template', 'issuedBy']);

        return view('admin.pages.certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        if (!$certificate->canBeDownloaded()) {
            return back()->with('error', 'لا يمكن تحميل هذه الشهادة');
        }

        $certificate->incrementDownloadCount();

        return response()->download(
            storage_path('app/public/' . $certificate->pdf_path),
            $certificate->certificate_number . '.pdf'
        );
    }

    public function revoke(Request $request, Certificate $certificate)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->certificateService->revokeCertificate(
                $certificate,
                $request->reason,
                auth()->id()
            );

            return back()->with('success', 'تم إلغاء الشهادة بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function reissue(Certificate $certificate)
    {
        try {
            $this->certificateService->reissueCertificate($certificate, auth()->id());

            return back()->with('success', 'تم إعادة إصدار الشهادة بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function destroy(Certificate $certificate)
    {
        try {
            $certificate->delete();

            return redirect()->route('admin.certificates.index')
                ->with('success', 'تم حذف الشهادة بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function bulkIssue(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'template_id' => 'required|exists:certificate_templates,id',
        ]);

        try {
            $course = Course::findOrFail($request->course_id);
            $template = CertificateTemplate::findOrFail($request->template_id);

            $result = $this->certificateService->issueAutomaticCertificates($course, $template);

            return back()->with('success', "تم إصدار {$result['total_issued']} شهادة بنجاح. فشل {$result['total_failed']} محاولة.");
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
