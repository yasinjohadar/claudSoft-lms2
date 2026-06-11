<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\StudentGift;
use App\Models\StudentGiftRecipient;
use App\Models\User;
use App\Services\Gamification\BadgeManualAwardService;
use App\Services\StudentGifts\StudentGiftGrantService;
use App\Services\Storage\StorageHelperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentGiftController extends Controller
{
    public function __construct(
        protected StudentGiftGrantService $grantService,
        protected StorageHelperService $storageHelper
    ) {}

    public function index(Request $request)
    {
        $query = StudentGift::query()->withCount('recipients');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), [
            StudentGift::STATUS_DRAFT,
            StudentGift::STATUS_GRANTED,
            StudentGift::STATUS_REVOKED,
        ], true)) {
            $query->where('status', $request->input('status'));
        }

        $gifts = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => StudentGift::query()->count(),
            'draft' => StudentGift::query()->where('status', StudentGift::STATUS_DRAFT)->count(),
            'granted' => StudentGift::query()->where('status', StudentGift::STATUS_GRANTED)->count(),
            'recipients' => StudentGiftRecipient::query()->count(),
        ];

        return view('admin.pages.student-gifts.index', compact('gifts', 'stats'));
    }

    public function create()
    {
        return view('admin.pages.student-gifts.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validator = $this->validateGiftRequest($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $this->giftAttributesFromRequest($request);
        $data['status'] = StudentGift::STATUS_DRAFT;
        $data['created_by'] = auth()->id();
        $data['target_type'] = $request->input('target_type');
        $data['target_payload'] = $this->targetPayloadFromRequest($request);

        $gift = StudentGift::create($data);
        $this->handleUploads($request, $gift);

        return redirect()
            ->route('admin.gifts.show', $gift)
            ->with('success', 'تم إنشاء الهدية كمسودة. يمكنك مراجعتها ثم منحها للطلاب.');
    }

    public function show(StudentGift $gift)
    {
        $gift->loadCount('recipients');
        $gift->load(['grantedBy', 'createdBy']);
        $recipients = $gift->recipients()->with('student')->latest('granted_at')->paginate(30);

        return view('admin.pages.student-gifts.show', compact('gift', 'recipients'));
    }

    public function edit(StudentGift $gift)
    {
        if ($gift->isRevoked()) {
            return redirect()
                ->route('admin.gifts.show', $gift)
                ->with('error', 'لا يمكن تعديل هدية ملغاة.');
        }

        return view('admin.pages.student-gifts.edit', array_merge(
            ['gift' => $gift],
            $this->formData()
        ));
    }

    public function update(Request $request, StudentGift $gift)
    {
        if ($gift->isRevoked()) {
            return redirect()
                ->route('admin.gifts.show', $gift)
                ->with('error', 'لا يمكن تعديل هدية ملغاة.');
        }

        $validator = $this->validateGiftRequest($request, $gift);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $this->giftAttributesFromRequest($request);
        $data['target_type'] = $request->input('target_type');
        $data['target_payload'] = $this->targetPayloadFromRequest($request);

        $gift->update($data);
        $this->handleUploads($request, $gift);

        return redirect()
            ->route('admin.gifts.show', $gift)
            ->with('success', 'تم تحديث الهدية بنجاح.');
    }

    public function destroy(StudentGift $gift)
    {
        if ($gift->isGranted()) {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن حذف هدية ممنوحة. يمكنك إلغاؤها بدلاً من ذلك.');
        }

        $gift->delete();

        return redirect()
            ->route('admin.gifts.index')
            ->with('success', 'تم حذف الهدية.');
    }

    public function previewRecipients(Request $request): JsonResponse
    {
        $validator = $this->validateTargetingRequest($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $gift = $request->filled('gift_id')
            ? StudentGift::query()->find($request->integer('gift_id'))
            : null;

        try {
            $preview = $this->grantService->previewFromRequest(
                $request->input('target_type'),
                $this->targetPayloadFromRequest($request),
                $gift
            );

            return response()->json($preview);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $hasIds = $request->filled('ids');
        $search = trim((string) $request->input('search', ''));

        if (! $hasIds && mb_strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $query = User::role('student');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        if ($hasIds) {
            $ids = collect(explode(',', $request->input('ids')))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->all();
            $query->whereIn('id', $ids);
        }

        $students = $query
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'name_ar', 'email'])
            ->map(fn (User $student) => [
                'id' => $student->id,
                'text' => $this->formatStudentLabel($student),
            ]);

        return response()->json(['results' => $students]);
    }

    public function grant(StudentGift $gift)
    {
        if ($gift->isRevoked()) {
            return redirect()->back()->with('error', 'لا يمكن منح هدية ملغاة.');
        }

        if (! $gift->target_type || empty($gift->target_payload)) {
            return redirect()->back()->with('error', 'يرجى تحديد الاستهداف قبل المنح.');
        }

        try {
            $result = $this->grantService->grant($gift, auth()->id());

            $message = "تم منح الهدية لـ {$result['granted']} طالب.";
            if ($result['skipped'] > 0) {
                $message .= " تم تخطي {$result['skipped']} (يمتلكونها مسبقاً).";
            }

            return redirect()->route('admin.gifts.show', $gift)->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function regrant(StudentGift $gift)
    {
        if (! $gift->target_type || empty($gift->target_payload)) {
            return redirect()->back()->with('error', 'يرجى تحديد الاستهداف قبل إعادة المنح.');
        }

        try {
            $wasRevoked = $gift->isRevoked();
            $result = $this->grantService->regrant($gift, auth()->id());

            if ($wasRevoked) {
                $message = "تم إعادة تفعيل الهدية. يمكن لـ {$result['restored']} مستلم الوصول إليها مجدداً.";
                if ($result['granted'] > 0) {
                    $message .= " تمت إضافة {$result['granted']} مستلم جديد.";
                }
            } elseif ($result['granted'] > 0) {
                $message = "تم إضافة {$result['granted']} مستلم جديد.";
                if ($result['skipped'] > 0) {
                    $message .= " {$result['skipped']} يمتلكونها مسبقاً.";
                }
            } else {
                $message = 'لا يوجد مستلمون جدد ضمن الاستهداف الحالي.';
            }

            return redirect()->route('admin.gifts.show', $gift)->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function revoke(StudentGift $gift)
    {
        $this->grantService->revoke($gift);

        return redirect()
            ->route('admin.gifts.show', $gift)
            ->with('success', 'تم إلغاء الهدية. لن يتمكن الطلاب من الوصول إليها.');
    }

    protected function formData(): array
    {
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $groups = CourseGroup::query()
            ->with('courses:id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return compact('courses', 'groups');
    }

    protected function validateGiftRequest(Request $request, ?StudentGift $gift = null): \Illuminate\Validation\Validator
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'content_mode' => ['required', Rule::in([StudentGift::CONTENT_UPLOAD, StudentGift::CONTENT_EXTERNAL])],
            'image' => 'nullable|image|max:5120',
            'target_type' => ['required', Rule::in(BadgeManualAwardService::TARGET_TYPES)],
        ];

        $contentMode = $request->input('content_mode', StudentGift::CONTENT_UPLOAD);

        if ($contentMode === StudentGift::CONTENT_EXTERNAL) {
            $rules['preview_url'] = 'nullable|url|max:2000';
            $rules['download_url'] = 'required|url|max:2000';
        } else {
            $previewRequired = ! $gift || ! $gift->preview_file_path;
            $downloadRequired = ! $gift || ! $gift->download_file_path;

            $rules['preview_file'] = ($previewRequired ? 'required' : 'nullable').'|file|max:51200';
            $rules['download_file'] = ($downloadRequired ? 'required' : 'nullable').'|file|max:102400';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $this->appendTargetingErrors($validator, $request);
        });

        return $validator;
    }

    protected function validateTargetingRequest(Request $request): \Illuminate\Validation\Validator
    {
        $rules = [
            'target_type' => ['required', Rule::in(BadgeManualAwardService::TARGET_TYPES)],
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $this->appendTargetingErrors($validator, $request);
        });

        return $validator;
    }

    protected function appendTargetingErrors($validator, Request $request): void
    {
        $targetType = $request->input('target_type');

        if ($targetType === 'single' && ! $request->filled('user_id')) {
            $validator->errors()->add('user_id', 'يرجى اختيار طالب.');
        }

        if ($targetType === 'multiple' && (! is_array($request->input('user_ids')) || count($request->input('user_ids', [])) === 0)) {
            $validator->errors()->add('user_ids', 'يرجى اختيار طالب واحد على الأقل.');
        }

        if ($targetType === 'group' && ! $request->filled('group_id')) {
            $validator->errors()->add('group_id', 'يرجى اختيار مجموعة.');
        }

        if ($targetType === 'multiple_groups' && (! is_array($request->input('group_ids')) || count($request->input('group_ids', [])) === 0)) {
            $validator->errors()->add('group_ids', 'يرجى اختيار مجموعة واحدة على الأقل.');
        }

        if ($targetType === 'course' && ! $request->filled('course_id')) {
            $validator->errors()->add('course_id', 'يرجى اختيار كورس.');
        }

        if ($targetType === 'course_group') {
            if (! $request->filled('course_id')) {
                $validator->errors()->add('course_id', 'يرجى اختيار كورس.');
            }
            if (! $request->filled('group_id')) {
                $validator->errors()->add('group_id', 'يرجى اختيار مجموعة.');
            }
        }
    }

    protected function giftAttributesFromRequest(Request $request): array
    {
        $data = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'content_mode' => $request->input('content_mode'),
        ];

        if ($request->input('content_mode') === StudentGift::CONTENT_EXTERNAL) {
            $data['preview_url'] = $request->input('preview_url');
            $data['download_url'] = $request->input('download_url');
            $data['preview_file_path'] = null;
            $data['preview_file_name'] = null;
            $data['preview_mime_type'] = null;
            $data['download_file_path'] = null;
            $data['download_file_name'] = null;
            $data['download_mime_type'] = null;
            $data['download_file_size'] = null;
        }

        return $data;
    }

    protected function targetPayloadFromRequest(Request $request): array
    {
        return [
            'user_id' => $request->input('user_id'),
            'user_ids' => $request->input('user_ids', []),
            'group_id' => $request->input('group_id'),
            'group_ids' => $request->input('group_ids', []),
            'course_id' => $request->input('course_id'),
        ];
    }

    protected function handleUploads(Request $request, StudentGift $gift): void
    {
        if ($request->hasFile('image')) {
            $path = 'gifts/images/'.Str::uuid().'.'.$request->file('image')->getClientOriginalExtension();
            $stored = $this->storageHelper->storeUploadedFile('public', $path, $request->file('image'), 'gift_image');
            if ($stored) {
                $gift->update(['image_path' => $stored]);
            }
        }

        if ($request->input('content_mode') !== StudentGift::CONTENT_UPLOAD) {
            return;
        }

        $updates = [];

        if ($request->hasFile('preview_file')) {
            $file = $request->file('preview_file');
            $path = 'gifts/previews/'.Str::uuid().'.'.$file->getClientOriginalExtension();
            $stored = $this->storageHelper->storeUploadedFile('public', $path, $file, 'gift_preview');
            if ($stored) {
                $updates['preview_file_path'] = $stored;
                $updates['preview_file_name'] = $file->getClientOriginalName();
                $updates['preview_mime_type'] = $file->getMimeType();
                $updates['preview_url'] = null;
            }
        }

        if ($request->hasFile('download_file')) {
            $file = $request->file('download_file');
            $path = 'gifts/downloads/'.Str::uuid().'.'.$file->getClientOriginalExtension();
            $stored = $this->storageHelper->storeUploadedFile('public', $path, $file, 'gift_download');
            if ($stored) {
                $updates['download_file_path'] = $stored;
                $updates['download_file_name'] = $file->getClientOriginalName();
                $updates['download_mime_type'] = $file->getMimeType();
                $updates['download_file_size'] = $file->getSize();
                $updates['download_url'] = null;
            }
        }

        if ($updates !== []) {
            $gift->update($updates);
        }
    }

    protected function formatStudentLabel(User $student): string
    {
        $label = $student->name;

        if ($student->name_ar) {
            $label .= ' ('.$student->name_ar.')';
        }

        return $label.' - '.$student->email;
    }
}
