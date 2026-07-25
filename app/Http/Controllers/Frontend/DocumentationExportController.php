<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DocumentationPage;
use App\Services\Documentation\DocumentationPdfExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DocumentationExportController extends Controller
{
    public function __construct(
        protected DocumentationPdfExportService $pdfExportService
    ) {}

    /**
     * Signed render URL for Browsershot (no auth — signature required).
     */
    public function render(DocumentationPage $documentation_page, string $context = 'public'): View
    {
        if ($context === 'admin') {
            $this->ensureAdminExportable($documentation_page);
        } else {
            $this->ensurePublicExportable($documentation_page);
        }

        return view('frontend.docs.show', [
            'category' => $documentation_page->category,
            'page' => $documentation_page,
            'pdfExport' => true,
            'forcedTheme' => 'light',
        ]);
    }

    public function download(DocumentationPage $documentation_page): Response|RedirectResponse
    {
        set_time_limit((int) config('browsershot.timeout', 120) + 30);

        $this->ensurePublicExportable($documentation_page);

        try {
            $pdf = $this->pdfExportService->export($documentation_page);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$this->pdfExportService->filename($documentation_page).'"',
                'Content-Length' => (string) strlen($pdf),
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->view('frontend.docs.pdf-export-error', [
                'message' => 'تعذّر إنشاء ملف PDF على السيرفر. عادةً السبب عدم توفر Chrome/Chromium داخل الحاوية.',
                'detail' => \Illuminate\Support\Str::limit($e->getMessage(), 500),
            ], 500);
        }
    }

    protected function ensurePublicExportable(DocumentationPage $documentation_page): void
    {
        $documentation_page->loadMissing('category');

        if (! $documentation_page->isPublished()) {
            abort(404);
        }

        if (! $documentation_page->category || ! $documentation_page->category->is_active) {
            abort(404);
        }
    }

    protected function ensureAdminExportable(DocumentationPage $documentation_page): void
    {
        $documentation_page->loadMissing('category');

        if (! $documentation_page->category) {
            abort(404);
        }
    }
}
