<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BunnyStreamLibrary;
use App\Models\Video;
use App\Services\Video\BunnyStreamVideoLinker;
use App\Support\BunnyStreamUrlParser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BunnyStreamLibraryController extends Controller
{
    public function index()
    {
        $libraries = BunnyStreamLibrary::query()
            ->withCount('videos')
            ->orderBy('library_name')
            ->get();

        $bunnyVideoCount = Video::query()
            ->where(function ($query) {
                $query->where('video_url', 'like', '%mediadelivery.net%')
                    ->orWhere('video_url', 'like', '%bunny.net%')
                    ->orWhere('video_url', 'like', '%b-cdn.net%')
                    ->orWhere('embed_code', 'like', '%mediadelivery.net%')
                    ->orWhere('embed_code', 'like', '%bunny.net%')
                    ->orWhere('embed_code', 'like', '%b-cdn.net%');
            })
            ->count();

        $linkedCount = Video::query()->whereNotNull('bunny_stream_library_id')->count();

        return view('admin.pages.bunny-stream-libraries.index', compact(
            'libraries',
            'bunnyVideoCount',
            'linkedCount'
        ));
    }

    public function create()
    {
        return view('admin.pages.bunny-stream-libraries.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateLibrary($request);

        BunnyStreamLibrary::create($validated);

        return redirect()
            ->route('bunny-stream-libraries.index')
            ->with('success', 'تم إضافة مكتبة Bunny بنجاح');
    }

    public function edit(BunnyStreamLibrary $bunnyStreamLibrary)
    {
        return view('admin.pages.bunny-stream-libraries.edit', [
            'library' => $bunnyStreamLibrary,
        ]);
    }

    public function update(Request $request, BunnyStreamLibrary $bunnyStreamLibrary)
    {
        $validated = $this->validateLibrary($request, $bunnyStreamLibrary);

        if (empty($validated['token_security_key'])) {
            unset($validated['token_security_key']);
        }

        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        $bunnyStreamLibrary->update($validated);

        return redirect()
            ->route('bunny-stream-libraries.index')
            ->with('success', 'تم تحديث مكتبة Bunny بنجاح');
    }

    public function destroy(BunnyStreamLibrary $bunnyStreamLibrary)
    {
        if ($bunnyStreamLibrary->videos()->exists()) {
            return redirect()
                ->route('bunny-stream-libraries.index')
                ->with('error', 'لا يمكن حذف المكتبة لأنها مرتبطة بفيديوهات. عطّلها بدلاً من الحذف.');
        }

        $bunnyStreamLibrary->delete();

        return redirect()
            ->route('bunny-stream-libraries.index')
            ->with('success', 'تم حذف مكتبة Bunny بنجاح');
    }

    public function syncVideos(BunnyStreamVideoLinker $linker)
    {
        $stats = $linker->linkAll();

        $message = "تم ربط {$stats['linked']} فيديو. "
            ."{$stats['already_linked']} كان مربوطاً مسبقاً. "
            ."{$stats['unresolved']} لم يُربط (مكتبة غير مسجلة أو رابط غير قياسي).";

        return redirect()
            ->route('bunny-stream-libraries.index')
            ->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLibrary(Request $request, ?BunnyStreamLibrary $library = null): array
    {
        $rules = [
            'library_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+$/',
                Rule::unique('bunny_stream_libraries', 'library_id')->ignore($library?->id),
            ],
            'library_name' => ['required', 'string', 'max:255'],
            'token_security_key' => [$library ? 'nullable' : 'required', 'string', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    /**
     * AJAX helper: suggest library from pasted Bunny URL.
     */
    public function detectFromUrl(Request $request)
    {
        $validated = $request->validate([
            'video_url' => ['nullable', 'string'],
            'embed_code' => ['nullable', 'string'],
        ]);

        $ids = BunnyStreamUrlParser::parseIds(
            $validated['video_url'] ?? null,
            $validated['embed_code'] ?? null
        );

        if (! $ids) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على library_id في الرابط',
            ]);
        }

        $library = BunnyStreamLibrary::query()
            ->where('library_id', $ids['library_id'])
            ->where('is_active', true)
            ->first();

        return response()->json([
            'success' => true,
            'library_id' => $ids['library_id'],
            'video_id' => $ids['video_id'],
            'bunny_stream_library_id' => $library?->id,
            'library_name' => $library?->displayLabel(),
            'registered' => (bool) $library,
        ]);
    }
}
