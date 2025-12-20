<?php

namespace App\Http\Controllers;

use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateVerificationController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index()
    {
        return view('certificates.verify.index');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $certificate = $this->certificateService->verifyCertificate($request->code);

        if (!$certificate) {
            return view('certificates.verify.result', [
                'verified' => false,
                'message' => 'الشهادة غير موجودة أو رمز التحقق غير صحيح',
            ]);
        }

        return view('certificates.verify.result', [
            'verified' => true,
            'certificate' => $certificate,
        ]);
    }

    public function show($code)
    {
        $certificate = $this->certificateService->verifyCertificate($code);

        if (!$certificate) {
            abort(404, 'الشهادة غير موجودة');
        }

        return view('certificates.verify.result', [
            'verified' => true,
            'certificate' => $certificate,
        ]);
    }
}
