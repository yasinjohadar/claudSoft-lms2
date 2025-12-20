@extends('student.layouts.master')

@section('page-title')
    تعديل {{ $work->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل العمل</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.works.index') }}">جدول أعمالي</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.works.show', $work) }}">{{ $work->title }}</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('student.works.update', $work) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Main Form -->
                    <div class="col-xl-8">
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-file-edit-line me-2"></i>معلومات العمل الأساسية
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Title -->
                                <div class="mb-3">
                                    <label class="form-label">عنوان العمل <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title', $work->title) }}" placeholder="مثال: نظام إدارة المكتبة" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Category & Course -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">التصنيف <span class="text-danger">*</span></label>
                                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                            <option value="">اختر التصنيف</option>
                                            @foreach($categories as $key => $category)
                                                <option value="{{ $key }}" {{ old('category', $work->category) == $key ? 'selected' : '' }}>
                                                    {{ $category['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">الدورة التدريبية</label>
                                        <select name="course_id" class="form-select @error('course_id') is-invalid @enderror">
                                            <option value="">اختر الدورة (اختياري)</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ old('course_id', $work->course_id) == $course->id ? 'selected' : '' }}>
                                                    {{ $course->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('course_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="5" placeholder="اكتب وصفاً تفصيلياً عن عملك...">{{ old('description', $work->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">صف فكرة العمل، الأهداف، والميزات الرئيسية</small>
                                </div>

                                <!-- Technologies -->
                                <div class="mb-3">
                                    <label class="form-label">التقنيات المستخدمة</label>
                                    <input type="text" name="technologies" class="form-control @error('technologies') is-invalid @enderror"
                                           value="{{ old('technologies', $work->technologies) }}" placeholder="مثال: Laravel, Vue.js, MySQL, Bootstrap">
                                    @error('technologies')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">اكتب التقنيات مفصولة بفواصل</small>
                                </div>

                                <!-- Tags -->
                                <div class="mb-3">
                                    <label class="form-label">الوسوم (Tags)</label>
                                    <div id="tags-container" class="mb-2"></div>
                                    <input type="text" id="tag-input" class="form-control" placeholder="اضغط Enter لإضافة وسم">
                                    <input type="hidden" name="tags[]" id="tags-hidden">
                                    <small class="text-muted">أضف وسوماً للمساعدة في البحث عن عملك</small>
                                </div>

                                <!-- Completion Date -->
                                <div class="mb-3">
                                    <label class="form-label">تاريخ الإنجاز</label>
                                    <input type="date" name="completion_date" class="form-control @error('completion_date') is-invalid @enderror"
                                           value="{{ old('completion_date', $work->completion_date ? $work->completion_date->format('Y-m-d') : '') }}">
                                    @error('completion_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Links Card -->
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-links-line me-2"></i>الروابط والمصادر
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- GitHub URL -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="ri-github-fill me-1"></i>رابط GitHub
                                    </label>
                                    <input type="url" name="github_url" class="form-control @error('github_url') is-invalid @enderror"
                                           value="{{ old('github_url', $work->github_url) }}" placeholder="https://github.com/username/project">
                                    @error('github_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Demo URL -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="ri-earth-line me-1"></i>رابط التجربة الحية (Demo)
                                    </label>
                                    <input type="url" name="demo_url" class="form-control @error('demo_url') is-invalid @enderror"
                                           value="{{ old('demo_url', $work->demo_url) }}" placeholder="https://demo.example.com">
                                    @error('demo_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Website URL -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="ri-global-line me-1"></i>رابط الموقع
                                    </label>
                                    <input type="url" name="website_url" class="form-control @error('website_url') is-invalid @enderror"
                                           value="{{ old('website_url', $work->website_url) }}" placeholder="https://example.com">
                                    @error('website_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Video URL -->
                                <div class="mb-0">
                                    <label class="form-label">
                                        <i class="ri-video-line me-1"></i>رابط فيديو توضيحي
                                    </label>
                                    <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror"
                                           value="{{ old('video_url', $work->video_url) }}" placeholder="https://youtube.com/watch?v=...">
                                    @error('video_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">رابط YouTube أو Vimeo</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-xl-4">
                        <!-- Image Upload -->
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-image-line me-2"></i>الصورة الرئيسية
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Current Image -->
                                @if($work->image)
                                    <div id="current-image" class="text-center mb-3">
                                        <p class="text-muted mb-2">الصورة الحالية:</p>
                                        <img src="{{ $work->image_url }}" alt="{{ $work->title }}" class="img-fluid rounded" style="max-height: 200px;">
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 w-100" onclick="removeCurrentImage()">
                                            <i class="ri-delete-bin-line me-1"></i>حذف الصورة الحالية
                                        </button>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">{{ $work->image ? 'تغيير الصورة' : 'رفع صورة' }}</label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*" id="image-input">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">الحد الأقصى: 2 ميجابايت</small>
                                </div>

                                <!-- Image Preview -->
                                <div id="image-preview" class="text-center d-none">
                                    <p class="text-muted mb-2">معاينة الصورة الجديدة:</p>
                                    <img src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2 w-100" onclick="removeImage()">
                                        <i class="ri-delete-bin-line me-1"></i>إزالة
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Work Status Info -->
                        <div class="card custom-card bg-light">
                            <div class="card-body">
                                <h6 class="mb-2">
                                    <i class="ri-information-line me-1"></i>حالة العمل الحالية
                                </h6>
                                @php
                                    $statuses = \App\Models\StudentWork::getStatuses();
                                    $currentStatus = $statuses[$work->status];
                                @endphp
                                <span class="badge bg-{{ $currentStatus['color'] }} mb-2">
                                    <i class="{{ $currentStatus['icon'] }} me-1"></i>
                                    {{ $currentStatus['name'] }}
                                </span>

                                @if($work->status === 'approved')
                                    <p class="text-muted mb-0" style="font-size: 13px;">
                                        <i class="ri-lock-line me-1"></i>
                                        العمل معتمد. أي تعديل سيتطلب موافقة جديدة من المدرس.
                                    </p>
                                @elseif($work->status === 'pending')
                                    <p class="text-muted mb-0" style="font-size: 13px;">
                                        <i class="ri-time-line me-1"></i>
                                        العمل قيد المراجعة. يمكنك التعديل ولكن سيتم إعادته لحالة المسودة.
                                    </p>
                                @elseif($work->status === 'rejected')
                                    <p class="text-muted mb-0" style="font-size: 13px;">
                                        <i class="ri-error-warning-line me-1"></i>
                                        يمكنك إجراء التعديلات المطلوبة وإعادة التقديم.
                                    </p>
                                @else
                                    <p class="text-muted mb-0" style="font-size: 13px;">
                                        <i class="ri-draft-line me-1"></i>
                                        يمكنك التعديل بحرية ثم تقديمه للمراجعة عند الانتهاء.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line me-1"></i>حفظ التعديلات
                                    </button>
                                    <a href="{{ route('student.works.show', $work) }}" class="btn btn-outline-secondary">
                                        <i class="ri-close-line me-1"></i>إلغاء
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Help Card -->
                        <div class="card custom-card bg-light">
                            <div class="card-body">
                                <h6 class="mb-2"><i class="ri-lightbulb-line me-1"></i>نصائح</h6>
                                <ul class="mb-0" style="font-size: 13px;">
                                    <li class="mb-1">راجع ملاحظات المدرس إن وجدت</li>
                                    <li class="mb-1">تأكد من تحديث جميع المعلومات</li>
                                    <li class="mb-1">أضف أي تحسينات أو ميزات جديدة</li>
                                    <li class="mb-0">احفظ عملك قبل المغادرة</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@stop

@section('script')
<script>
    // Initialize tags from existing data
    let tags = @json($work->tags ?? []);
    const tagInput = document.getElementById('tag-input');
    const tagsContainer = document.getElementById('tags-container');
    const tagsHidden = document.getElementById('tags-hidden');

    // Load existing tags on page load
    updateTags();

    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const tag = this.value.trim();
            if (tag && !tags.includes(tag)) {
                tags.push(tag);
                updateTags();
                this.value = '';
            }
        }
    });

    function updateTags() {
        tagsContainer.innerHTML = tags.map((tag, index) => `
            <span class="badge bg-primary me-1 mb-1">
                ${tag}
                <i class="ri-close-line ms-1" style="cursor: pointer;" onclick="removeTag(${index})"></i>
            </span>
        `).join('');

        // Update hidden input
        tagsHidden.value = JSON.stringify(tags);
    }

    function removeTag(index) {
        tags.splice(index, 1);
        updateTags();
    }

    // Image preview
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    const currentImage = document.getElementById('current-image');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.querySelector('img').src = e.target.result;
                imagePreview.classList.remove('d-none');
                if(currentImage) currentImage.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    function removeImage() {
        imageInput.value = '';
        imagePreview.classList.add('d-none');
        imagePreview.querySelector('img').src = '';
        if(currentImage) currentImage.classList.remove('d-none');
    }

    function removeCurrentImage() {
        if(confirm('هل أنت متأكد من حذف الصورة الحالية؟')) {
            currentImage.classList.add('d-none');
            // You might want to add a hidden input to mark image for deletion
        }
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const title = document.querySelector('input[name="title"]').value;
        const category = document.querySelector('select[name="category"]').value;

        if (!title || !category) {
            e.preventDefault();
            alert('الرجاء ملء جميع الحقول المطلوبة');
        }
    });
</script>
@stop
