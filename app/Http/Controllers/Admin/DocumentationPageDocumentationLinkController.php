<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttachDocumentationPageFromDocsRequest;
use App\Models\DocumentationPage;
use App\Services\Documentation\DocumentationPageLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class DocumentationPageDocumentationLinkController extends Controller
{
    public function __construct(
        protected DocumentationPageLinkService $linkService
    ) {}

    public function store(
        AttachDocumentationPageFromDocsRequest $request,
        DocumentationPage $documentation_page
    ): RedirectResponse|JsonResponse {
        try {
            $links = $this->linkService->attachFromDocumentationPage(
                $documentation_page,
                $request->validated(),
                $request->user()?->id
            );

            $message = 'تم ربط التوثيق بـ '.$links->count().' كورس/روابط بنجاح.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.docs.pages.index'),
                ]);
            }

            return redirect()
                ->route('admin.docs.pages.index')
                ->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذّر ربط التوثيق.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذّر ربط التوثيق: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'تعذّر ربط التوثيق: '.$e->getMessage())
                ->withInput();
        }
    }
}
