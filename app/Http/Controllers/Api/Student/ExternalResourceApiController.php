<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExternalResourceApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        $query = $this->filteredQuery($request);
        $paginator = $query->paginate($perPage);

        $resources = collect($paginator->items())->map(function (Resource $r) {
            return [
                'id' => (int) $r->id,
                'title' => (string) $r->title,
                'description' => $r->description !== null ? (string) $r->description : null,
                'resource_type' => (string) $r->resource_type,
                'classification' => $r->classification !== null ? (string) $r->classification : null,
                'resource_url' => $r->resource_url !== null ? (string) $r->resource_url : null,
                'allow_download' => (bool) $r->allow_download,
                'open_url' => url('/api/student/external-resources/'.$r->id.'/open'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'resources' => $resources,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    /**
     * فتح/تحميل المورد (نفس منطق لوحة الويب).
     */
    public function open(Request $request, Resource $resource)
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

            return response()->json([
                'success' => false,
                'message' => 'التحميل غير مسموح لهذا المورد.',
            ], 403);
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
