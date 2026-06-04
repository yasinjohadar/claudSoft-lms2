<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * الموارد الخارجية للطالب: كل مورد نطاقه ليس «خاص» + منشور وظاهر ومتاح.
 */
class ExternalResourceController extends Controller
{
    public function index(Request $request)
    {
        $resources = $this->filteredQuery($request)->paginate(12)->withQueryString();

        $baseQuery = Resource::query()->forStudentExternalLibrary();
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'links' => (clone $baseQuery)->where(function ($q) {
                $q->where('resource_source', 'url')->orWhere('resource_type', 'external_sites');
            })->count(),
            'files' => (clone $baseQuery)->whereNotNull('file_path')->count(),
            'filtered' => $resources->total(),
        ];

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('student.pages.external-resources.partials.grid', compact('resources'))->render();

            return response()->json([
                'html' => $html,
                'meta' => [
                    'total' => $resources->total(),
                    'current_page' => $resources->currentPage(),
                    'last_page' => $resources->lastPage(),
                    'from' => $resources->firstItem(),
                    'to' => $resources->lastItem(),
                ],
            ]);
        }

        return view('student.pages.external-resources.index', compact('resources', 'stats'));
    }

    public function access(Resource $resource)
    {
        if (! $resource->isAccessibleInExternalLibrary()) {
            abort(404);
        }

        if (! empty($resource->resource_url)) {
            $linkOnly = empty($resource->file_path);
            $urlSource = $resource->resource_source === 'url';
            $externalType = $resource->resource_type === 'external_sites';
            if ($urlSource || $externalType || $linkOnly) {
                return redirect()->away($resource->resource_url);
            }
        }

        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            if ($resource->allow_download) {
                $resource->incrementDownloadCount();

                return Storage::disk('public')->download(
                    $resource->file_path,
                    $resource->file_name ?: basename($resource->file_path)
                );
            }

            if (in_array($resource->resource_type, ['pdf', 'image'], true)) {
                return redirect()->away(asset('storage/'.$resource->file_path));
            }

            return redirect()
                ->route('student.external-resources.index')
                ->with('error', 'التحميل غير مسموح لهذا المورد. يمكنك طلب الملف من الإدارة.');
        }

        abort(404);
    }

    private function filteredQuery(Request $request)
    {
        $query = Resource::query()->forStudentExternalLibrary();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('file_name', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('resource_type')) {
            $type = $request->input('resource_type');
            if (in_array($type, Resource::resourceTypeKeys(), true)) {
                $query->where('resource_type', $type);
            }
        }

        if ($request->filled('classification')) {
            $c = $request->input('classification');
            if (in_array($c, Resource::classificationKeys(), true)) {
                $query->where('classification', $c);
            }
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'title') {
            $query->orderBy('title');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query;
    }
}
