@php
    $work = $work ?? null;
    $isEdit = $work !== null;
    $cancelUrl = $cancelUrl ?? ($isEdit ? route('student.works.show', $work) : route('student.works.index'));
    $submitLabel = $submitLabel ?? ($isEdit ? 'حفظ التعديلات' : 'حفظ العمل');

    $tips = $isEdit
        ? ['راجع ملاحظات المدرس إن وُجدت', 'تأكد من تحديث جميع المعلومات', 'أضف أي تحسينات أو ميزات جديدة', 'احفظ عملك قبل المغادرة']
        : ['اختر عنواناً واضحاً ومميزاً', 'أضف وصفاً تفصيلياً للعمل', 'ارفع صورة عالية الجودة', 'أضف روابط GitHub والتجربة', 'استخدم الوسوم للمساعدة في البحث'];
@endphp

<div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">
            <i class="fe fe-image me-2 text-primary"></i>الصورة الرئيسية
        </h6>
    </div>
    <div class="card-body pt-3">
        @if($isEdit && $work->image)
            <div id="current-image" class="student-work-form__image-preview mb-3">
                <p class="text-muted fs-12 mb-2">الصورة الحالية</p>
                <img src="{{ $work->image_url }}" alt="{{ $work->title }}" class="student-work-form__image-preview-img">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill mt-2 w-100" onclick="removeCurrentImage()">
                    <i class="fe fe-trash-2 me-1"></i>إخفاء الصورة الحالية
                </button>
            </div>
        @endif

        <label class="student-work-form__upload-zone" for="image-input">
            <span class="student-work-form__upload-icon"><i class="fe fe-upload-cloud"></i></span>
            <span class="student-work-form__upload-title">{{ $isEdit && $work->image ? 'تغيير الصورة' : 'رفع صورة' }}</span>
            <span class="student-work-form__upload-hint">PNG, JPG — الحد الأقصى 2 ميجابايت</span>
            <input type="file" name="image" class="d-none @error('image') is-invalid @enderror"
                   accept="image/*" id="image-input">
        </label>
        @error('image')
            <div class="text-danger fs-12 mt-2">{{ $message }}</div>
        @enderror

        <div id="image-preview" class="student-work-form__image-preview d-none mt-3">
            <p class="text-muted fs-12 mb-2">معاينة الصورة</p>
            <img src="" alt="Preview" class="student-work-form__image-preview-img">
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill mt-2 w-100" onclick="removeImage()">
                <i class="fe fe-x me-1"></i>إزالة المعاينة
            </button>
        </div>
    </div>
</div>

@if(!$isEdit)
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">
                <i class="fe fe-settings me-2 text-primary"></i>الإعدادات
            </h6>
        </div>
        <div class="card-body pt-3">
            <label class="form-label fw-semibold">حالة العمل</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>
                    مسودة (يمكنك التعديل لاحقاً)
                </option>
                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>
                    تقديم للمراجعة
                </option>
            </select>
            @error('status')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <p class="text-muted fs-12 mb-0 mt-2">
                <i class="fe fe-info me-1"></i>
                المسودة: يمكنك التعديل في أي وقت. التقديم للمراجعة يرسل العمل للإدارة.
            </p>
        </div>
    </div>
@else
    @php
        $statuses = \App\Models\StudentWork::getStatuses();
        $currentStatus = $statuses[$work->status] ?? ['name' => $work->status, 'color' => 'secondary', 'icon' => 'fe-help-circle'];
        $statusIcon = match ($work->status) {
            'approved' => 'fe-check-circle',
            'pending' => 'fe-clock',
            'rejected' => 'fe-x-circle',
            default => 'fe-edit-3',
        };
        $statusNote = match ($work->status) {
            'approved' => 'العمل معتمد. أي تعديل قد يتطلب موافقة جديدة.',
            'pending' => 'العمل قيد المراجعة. يمكنك التعديل في أي وقت.',
            'rejected' => 'يمكنك إجراء التعديلات المطلوبة وإعادة التقديم.',
            default => 'يمكنك التعديل بحرية ثم تقديمه للمراجعة عند الانتهاء.',
        };
    @endphp
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">
                <i class="fe fe-activity me-2 text-primary"></i>حالة العمل
            </h6>
        </div>
        <div class="card-body pt-3">
            <span class="badge bg-{{ $currentStatus['color'] }}-transparent text-{{ $currentStatus['color'] }} mb-2">
                <i class="fe {{ $statusIcon }} me-1"></i>{{ $currentStatus['name'] }}
            </span>
            <p class="text-muted fs-12 mb-0">{{ $statusNote }}</p>
            @if($work->status === 'draft')
                <form action="{{ route('student.works.submit', $work) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success rounded-pill w-100"
                            onclick="return confirm('هل أنت متأكد من تقديم هذا العمل للمراجعة؟')">
                        <i class="fe fe-send me-1"></i>تقديم للمراجعة
                    </button>
                </form>
            @endif
        </div>
    </div>
@endif

<div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
    <div class="card-body">
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary rounded-pill">
                <i class="fe fe-save me-1"></i>{{ $submitLabel }}
            </button>
            <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary rounded-pill">
                <i class="fe fe-x me-1"></i>إلغاء
            </a>
        </div>
    </div>
</div>

<div class="card custom-card group-show-members-card dashboard-fade-in student-work-form__tips">
    <div class="card-body">
        <h6 class="mb-3">
            <i class="fe fe-zap text-warning me-2"></i>نصائح
        </h6>
        <ul class="student-work-form__tips-list mb-0">
            @foreach($tips as $tip)
                <li>{{ $tip }}</li>
            @endforeach
        </ul>
    </div>
</div>
