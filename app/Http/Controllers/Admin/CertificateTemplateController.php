<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:certificate-templates.view', ['only' => ['index', 'show']]);
        $this->middleware('permission:certificate-templates.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:certificate-templates.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:certificate-templates.delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $templates = CertificateTemplate::ordered()->paginate(20);

        return view('admin.pages.certificate-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.pages.certificate-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:html,image',
            'template_file' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'html_content' => 'nullable|string',
            'orientation' => 'required|in:portrait,landscape',
            'page_size' => 'required|string',
            'min_completion_percentage' => 'required|integer|min:0|max:100',
            'min_attendance_percentage' => 'nullable|integer|min:0|max:100',
            'min_final_exam_score' => 'nullable|integer|min:0|max:100',
            'expiry_months' => 'nullable|integer|min:1',
        ]);

        $data = $request->except(['template_file']);

        if ($request->hasFile('template_file')) {
            $data['template_file'] = $request->file('template_file')->store('certificates/templates', 'public');
        }

        $template = CertificateTemplate::create($data);

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'تم إنشاء القالب بنجاح');
    }

    public function show(CertificateTemplate $certificateTemplate)
    {
        $certificateTemplate->load('certificates');
        $template = $certificateTemplate;

        return view('admin.pages.certificate-templates.show', compact('template'));
    }

    public function edit(CertificateTemplate $certificateTemplate)
    {
        $template = $certificateTemplate;

        return view('admin.pages.certificate-templates.edit', compact('template'));
    }

    public function update(Request $request, CertificateTemplate $certificateTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:html,image',
            'template_file' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except(['template_file']);

        if ($request->hasFile('template_file')) {
            // حذف الملف القديم
            if ($certificateTemplate->template_file) {
                Storage::disk('public')->delete($certificateTemplate->template_file);
            }

            $data['template_file'] = $request->file('template_file')->store('certificates/templates', 'public');
        }

        $certificateTemplate->update($data);

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'تم تحديث القالب بنجاح');
    }

    public function destroy(CertificateTemplate $certificateTemplate)
    {
        try {
            if ($certificateTemplate->template_file) {
                Storage::disk('public')->delete($certificateTemplate->template_file);
            }

            $certificateTemplate->delete();

            return redirect()->route('admin.certificate-templates.index')
                ->with('success', 'تم حذف القالب بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function setDefault(CertificateTemplate $certificateTemplate)
    {
        $certificateTemplate->setAsDefault();

        return back()->with('success', 'تم تعيين القالب كافتراضي');
    }

    public function preview(CertificateTemplate $certificateTemplate)
    {
        $template = $certificateTemplate;

        // بيانات تجريبية للمعاينة
        $sampleData = [
            'student_name' => 'أحمد محمد السعيد',
            'course_name' => 'تطوير تطبيقات الويب المتقدمة',
            'certificate_number' => 'CERT-2025-00001',
            'issue_date' => now()->format('Y-m-d'),
            'issue_date_ar' => now()->locale('ar')->translatedFormat('d F Y'),
            'completion_date' => now()->format('Y-m-d'),
            'completion_percentage' => '100',
            'attendance_percentage' => '95',
            'final_exam_score' => '92',
            'verification_code' => 'ABC123XYZ',
            'course_hours' => '40',
        ];

        return view('admin.pages.certificate-templates.preview', compact('template', 'sampleData'));
    }
}
