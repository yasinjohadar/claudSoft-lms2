@extends('admin.layouts.master')

@section('page-title')
    تعديل الفيديو
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل الفيديو: {{ $video->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('videos.index') }}">الفيديوهات</a></li>
                            <li class="breadcrumb-item active">تعديل الفيديو</li>
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
                            <form action="{{ route('videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Basic Information -->
                                <div class="row gy-3">
                                    <!-- Title -->
                                    <div class="col-xl-12">
                                        <label class="form-label">عنوان الفيديو <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title', $video->title) }}" required placeholder="أدخل عنوان الفيديو">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="col-xl-12">
                                        <label class="form-label">الوصف</label>
                                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                                                  placeholder="أدخل وصف الفيديو">{{ old('description', $video->description) }}</textarea>
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
                                                <option value="{{ $type }}" {{ old('video_type', $video->video_type) == $type ? 'selected' : '' }}>
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
                                               value="{{ old('duration', $video->duration) }}" min="0" placeholder="مثال: 3600">
                                        @error('duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Current Video Display -->
                                    @if($video->video_url || $video->video_path)
                                        <div class="col-xl-12">
                                            <div class="card border-info">
                                                <div class="card-header bg-info-transparent">
                                                    <h6 class="mb-0"><i class="fas fa-video me-2"></i>الفيديو الحالي</h6>
                                                </div>
                                                <div class="card-body">
                                                    @if($video->video_type == 'youtube' || $video->video_type == 'vimeo' || $video->video_type == 'external')
                                                        @if($video->video_type == 'youtube')
                                                            @php
                                                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $video->video_url, $match);
                                                                $youtubeId = isset($match[1]) ? $match[1] : null;
                                                            @endphp
                                                            @if($youtubeId)
                                                                <iframe width="100%" height="400" src="https://www.youtube.com/embed/{{ $youtubeId }}" frameborder="0" allowfullscreen></iframe>
                                                            @else
                                                                <p class="text-muted">رابط YouTube: <a href="{{ $video->video_url }}" target="_blank">{{ $video->video_url }}</a></p>
                                                            @endif
                                                        @elseif($video->video_type == 'vimeo')
                                                            @php
                                                                preg_match('/vimeo\.com\/(\d+)/i', $video->video_url, $match);
                                                                $vimeoId = isset($match[1]) ? $match[1] : null;
                                                            @endphp
                                                            @if($vimeoId)
                                                                <iframe width="100%" height="400" src="https://player.vimeo.com/video/{{ $vimeoId }}" frameborder="0" allowfullscreen></iframe>
                                                            @else
                                                                <p class="text-muted">رابط Vimeo: <a href="{{ $video->video_url }}" target="_blank">{{ $video->video_url }}</a></p>
                                                            @endif
                                                        @else
                                                            <p class="text-muted">رابط الفيديو: <a href="{{ $video->video_url }}" target="_blank">{{ $video->video_url }}</a></p>
                                                        @endif
                                                    @elseif($video->video_path)
                                                        <video width="100%" height="400" controls>
                                                            <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                                            متصفحك لا يدعم تشغيل الفيديو.
                                                        </video>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Video URL (for youtube, vimeo, external) -->
                                    <div class="col-xl-12" id="video_url_field" style="display: none;">
                                        <label class="form-label">رابط الفيديو <span class="text-danger">*</span></label>
                                        <input type="url" name="video_url" id="video_url" class="form-control @error('video_url') is-invalid @enderror"
                                               value="{{ old('video_url', $video->video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                                        <small class="text-muted">اتركه فارغاً للاحتفاظ بالرابط الحالي</small>
                                        @error('video_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Video File (for upload) -->
                                    <div class="col-xl-12" id="video_file_field" style="display: none;">
                                        <label class="form-label">ملف الفيديو</label>
                                        <input type="file" name="video_file" id="video_file" class="form-control @error('video_file') is-invalid @enderror"
                                               accept="video/mp4,video/mov,video/avi,video/wmv">
                                        <small class="text-muted">الحد الأقصى: 500 ميجا. الصيغ المدعومة: mp4, mov, avi, wmv. اتركه فارغاً للاحتفاظ بالملف الحالي</small>
                                        @error('video_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Current Thumbnail -->
                                    @if($video->thumbnail)
                                        <div class="col-xl-12">
                                            <label class="form-label">الصورة المصغرة الحالية</label>
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="Thumbnail" style="max-width: 200px; border-radius: 8px;">
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Thumbnail -->
                                    <div class="col-xl-12">
                                        <label class="form-label">صورة مصغرة جديدة</label>
                                        <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                                               accept="image/jpeg,image/png,image/jpg,image/gif">
                                        <small class="text-muted">الحد الأقصى: 2 ميجا. الصيغ المدعومة: jpeg, png, jpg, gif. اتركه فارغاً للاحتفاظ بالصورة الحالية</small>
                                        @error('thumbnail')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Settings -->
                                    <div class="col-xl-12">
                                        <div class="card border">
                                            <div class="card-header">
                                                <h6 class="mb-0">إعدادات الفيديو</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row gy-3">
                                                    <!-- Is Published -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published"
                                                                   value="1" {{ old('is_published', $video->is_published) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_published">
                                                                منشور
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Is Visible -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                                                   value="1" {{ old('is_visible', $video->is_visible) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_visible">
                                                                مرئي
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Allow Download -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="allow_download" id="allow_download"
                                                                   value="1" {{ old('allow_download', $video->allow_download) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="allow_download">
                                                                السماح بالتحميل
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Allow Speed Control -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="allow_speed_control" id="allow_speed_control"
                                                                   value="1" {{ old('allow_speed_control', $video->allow_speed_control) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="allow_speed_control">
                                                                السماح بالتحكم في السرعة
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Require Watch Complete -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="require_watch_complete" id="require_watch_complete"
                                                                   value="1" {{ old('require_watch_complete', $video->require_watch_complete) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="require_watch_complete">
                                                                يتطلب مشاهدة كاملة
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Available From -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">متاح من</label>
                                                        <input type="datetime-local" name="available_from" class="form-control"
                                                               value="{{ old('available_from', $video->available_from ? $video->available_from->format('Y-m-d\TH:i') : '') }}">
                                                    </div>

                                                    <!-- Available Until -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">متاح حتى</label>
                                                        <input type="datetime-local" name="available_until" class="form-control"
                                                               value="{{ old('available_until', $video->available_until ? $video->available_until->format('Y-m-d\TH:i') : '') }}">
                                                    </div>

                                                    <!-- Sort Order -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">ترتيب العرض</label>
                                                        <input type="number" name="sort_order" class="form-control"
                                                               value="{{ old('sort_order', $video->sort_order) }}" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-xl-12">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>حفظ التعديلات
                                            </button>
                                            <a href="{{ route('videos.show', $video->id) }}" class="btn btn-light">
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
                    // Only require if field is empty (new URL)
                    if (!videoUrlInput.value) {
                        videoUrlInput.setAttribute('required', 'required');
                    }
                } else if (selectedType === 'upload') {
                    videoFileField.style.display = 'block';
                    // File is optional when editing (can keep existing)
                }
            }

            // Initial check
            toggleFields();

            // Listen for changes
            videoTypeSelect.addEventListener('change', toggleFields);
        });
    </script>
@stop

