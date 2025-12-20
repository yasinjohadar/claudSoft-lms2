@extends('admin.layouts.master')

@section('page-title')
    إضافة فيديو جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                            <h5 class="page-title fs-21 mb-1">إضافة فيديو جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            @if(isset($section) && $section)
                                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('courses.show', $section->course_id) }}">{{ $section->course->title ?? 'الكورس' }}</a></li>
                                <li class="breadcrumb-item active">إضافة فيديو - {{ $section->title }}</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('videos.index') }}">الفيديوهات</a></li>
                                <li class="breadcrumb-item active">إضافة فيديو</li>
                            @endif
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Video Form -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الفيديو</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                @if(isset($sectionId) && $sectionId)
                                    <input type="hidden" name="section_id" value="{{ $sectionId }}">
                                @endif
                                
                                @if(isset($courseId) && $courseId)
                                    <input type="hidden" name="course_id" value="{{ $courseId }}">
                                @endif

                                <!-- Basic Information -->
                                <div class="row gy-3">
                                    <!-- Title -->
                                    <div class="col-xl-12">
                                        <label class="form-label">عنوان الفيديو <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title') }}" required placeholder="أدخل عنوان الفيديو">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="col-xl-12">
                                        <label class="form-label">الوصف</label>
                                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                                                  placeholder="أدخل وصف الفيديو">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Video Type -->
                                    <div class="col-xl-6">
                                        <label class="form-label">نوع الفيديو <span class="text-danger">*</span></label>
                                        <select name="video_type" id="video_type" class="form-select @error('video_type') is-invalid @enderror" required>
                                            <option value="">اختر نوع الفيديو</option>
                                            @foreach($videoTypes as $type)
                                                <option value="{{ $type }}" {{ old('video_type') == $type ? 'selected' : '' }}>
                                                    @if($type == 'upload') رفع ملف
                                                    @elseif($type == 'youtube') يوتيوب
                                                    @elseif($type == 'vimeo') فيميو
                                                    @elseif($type == 'external') رابط خارجي
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('video_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Duration -->
                                    <div class="col-xl-6">
                                        <label class="form-label">المدة (بالثواني)</label>
                                        <input type="number" name="duration" class="form-control @error('duration') is-invalid @enderror"
                                               value="{{ old('duration') }}" min="0" placeholder="مثال: 3600">
                                        @error('duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Video URL (for youtube, vimeo, external) -->
                                    <div class="col-xl-12" id="video_url_field" style="display: none;">
                                        <label class="form-label">رابط الفيديو <span class="text-danger">*</span></label>
                                        <input type="url" name="video_url" id="video_url" class="form-control @error('video_url') is-invalid @enderror"
                                               value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=...">
                                        @error('video_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Video File (for upload) -->
                                    <div class="col-xl-12" id="video_file_field" style="display: none;">
                                        <label class="form-label">ملف الفيديو <span class="text-danger">*</span></label>
                                        <input type="file" name="video_file" id="video_file" class="form-control @error('video_file') is-invalid @enderror"
                                               accept="video/mp4,video/mov,video/avi,video/wmv">
                                        <small class="text-muted">الحد الأقصى: 500 ميجا. الصيغ المدعومة: mp4, mov, avi, wmv</small>
                                        @error('video_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Thumbnail -->
                                    <div class="col-xl-12">
                                        <label class="form-label">صورة مصغرة</label>
                                        <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                                               accept="image/jpeg,image/png,image/jpg,image/gif">
                                        <small class="text-muted">الحد الأقصى: 2 ميجا. الصيغ المدعومة: jpeg, png, jpg, gif</small>
                                        @error('thumbnail')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Published Status -->
                                    <div class="col-xl-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published"
                                                   value="1" {{ old('is_published') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_published">
                                                نشر الفيديو مباشرة
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-xl-12">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>حفظ الفيديو
                                            </button>
                                            <a href="{{ route('videos.index') }}" class="btn btn-light">
                                                <i class="fas fa-times me-2"></i>إلغاء
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const videoTypeSelect = document.getElementById('video_type');
            const videoUrlField = document.getElementById('video_url_field');
            const videoFileField = document.getElementById('video_file_field');
            const videoUrlInput = document.getElementById('video_url');
            const videoFileInput = document.getElementById('video_file');

            function toggleFields() {
                const selectedType = videoTypeSelect.value;

                // Hide all fields first
                videoUrlField.style.display = 'none';
                videoFileField.style.display = 'none';
                videoUrlInput.removeAttribute('required');
                videoFileInput.removeAttribute('required');

                // Show relevant field based on type
                if (selectedType === 'youtube' || selectedType === 'vimeo' || selectedType === 'external') {
                    videoUrlField.style.display = 'block';
                    videoUrlInput.setAttribute('required', 'required');
                } else if (selectedType === 'upload') {
                    videoFileField.style.display = 'block';
                    videoFileInput.setAttribute('required', 'required');
                }
            }

            // Initial check
            toggleFields();

            // Listen for changes
            videoTypeSelect.addEventListener('change', toggleFields);
        });
    </script>
@stop
