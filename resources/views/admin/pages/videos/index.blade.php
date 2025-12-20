@extends('admin.layouts.master')

@section('page-title')
    مكتبة الفيديوهات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الفيديوهات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الفيديوهات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('videos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة فيديو جديد
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-video fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي الفيديوهات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalVideos ?? 0 }}</h4>
                                    <span class="badge bg-primary-transparent">فيديو</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">منشورة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $publishedCount ?? 0 }}</h4>
                                    <span class="badge bg-success-transparent">فيديو</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-clock fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي المدة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ gmdate('H:i', $totalDuration ?? 0) }}</h4>
                                    <span class="badge bg-info-transparent">ساعة:دقيقة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-eye fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">المشاهدات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalViews ?? 0 }}</h4>
                                    <span class="badge bg-warning-transparent">مشاهدة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('videos.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن فيديو...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">نوع الفيديو</label>
                                <select name="video_type" class="form-select">
                                    <option value="">جميع الأنواع</option>
                                    @foreach($videoTypes ?? [] as $type)
                                        <option value="{{ $type }}" {{ request('video_type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">حالة النشر</label>
                                <select name="is_published" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_published') == '1' ? 'selected' : '' }}>منشور</option>
                                    <option value="0" {{ request('is_published') == '0' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('videos.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Videos Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الفيديوهات</div>
                </div>
                <div class="card-body">
                    @if($videos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>الفيديو</th>
                                        <th>النوع</th>
                                        <th>المدة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($videos as $video)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($video->thumbnail)
                                                        <img src="{{ asset('storage/' . $video->thumbnail) }}"
                                                             alt="{{ $video->title }}"
                                                             class="avatar avatar-md rounded me-2"
                                                             style="object-fit: cover;">
                                                    @else
                                                        <span class="avatar avatar-md bg-primary-transparent me-2">
                                                            <i class="fas fa-video"></i>
                                                        </span>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ Str::limit($video->title, 40) }}</h6>
                                                        @if($video->description)
                                                            <small class="text-muted">{{ Str::limit($video->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'upload' => 'primary',
                                                        'youtube' => 'danger',
                                                        'vimeo' => 'info',
                                                        'external' => 'warning'
                                                    ];
                                                    $color = $typeColors[$video->video_type] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}-transparent">
                                                    {{ ucfirst($video->video_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($video->duration)
                                                    <span class="badge bg-info-transparent">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ gmdate('i:s', $video->duration) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($video->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-warning">مسودة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('videos.show', $video->id) }}"
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('videos.edit', $video->id) }}"
                                                       class="btn btn-sm btn-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-{{ $video->is_published ? 'warning' : 'success' }}"
                                                            onclick="togglePublish({{ $video->id }})"
                                                            title="{{ $video->is_published ? 'إلغاء النشر' : 'نشر' }}">
                                                        <i class="fas fa-{{ $video->is_published ? 'eye-slash' : 'paper-plane' }}"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="deleteVideo({{ $video->id }})"
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $videos->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-video fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد فيديوهات</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Toggle Publish Modal -->
    <div class="modal fade" id="togglePublishModal" tabindex="-1" aria-labelledby="togglePublishModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="avatar avatar-xl bg-info-transparent mx-auto mb-3">
                            <i class="fas fa-paper-plane fs-24 text-info"></i>
                        </div>
                        <h5 class="mb-2" id="togglePublishModalLabel">تغيير حالة النشر</h5>
                        <p class="text-muted mb-0" id="togglePublishMessage">هل تريد تغيير حالة النشر لهذا الفيديو؟</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmTogglePublish">
                        <i class="fas fa-check me-2"></i>تأكيد
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Video Modal -->
    <div class="modal fade" id="deleteVideoModal" tabindex="-1" aria-labelledby="deleteVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-trash-alt fa-3x"></i>
                        </span>
                    </div>
                    <h5 class="modal-title text-center mb-4 fw-bold" id="deleteVideoModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                        تأكيد حذف الفيديو
                    </h5>
                    <p class="text-muted mb-3" id="deleteVideoMessage">
                        هل أنت متأكد من حذف هذا الفيديو؟
                    </p>
                    
                    <!-- Warning Section (shown if video is used in modules) -->
                    <div id="videoUsageWarning" class="alert alert-warning text-start d-none mb-3" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                            <div>
                                <strong class="d-block mb-2">تحذير:</strong>
                                <p class="mb-2" id="warningMessageText"></p>
                                <div id="modulesList" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-danger small mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        لن يمكن التراجع عن هذه العملية.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteVideo">
                            <i class="fas fa-trash-alt me-2"></i>حذف نهائياً
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="avatar avatar-xl bg-success-transparent mx-auto mb-3" id="alertIconContainer">
                            <i class="fas fa-check-circle fs-24 text-success" id="alertIcon"></i>
                        </div>
                        <h5 class="mb-2" id="alertModalLabel">نجح</h5>
                        <p class="text-muted mb-0" id="alertMessage">تمت العملية بنجاح</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i>حسناً
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    let currentVideoId = null;
    let currentAction = null; // 'toggle' or 'delete'

    // Toggle Publish Status
    function togglePublish(videoId) {
        currentVideoId = videoId;
        currentAction = 'toggle';
        
        const modal = new bootstrap.Modal(document.getElementById('togglePublishModal'));
        modal.show();
    }

    // Delete Video
    function deleteVideo(videoId) {
        currentVideoId = videoId;
        currentAction = 'delete';
        
        // Reset modal content
        document.getElementById('videoUsageWarning').classList.add('d-none');
        document.getElementById('deleteVideoMessage').textContent = 'هل أنت متأكد من حذف هذا الفيديو؟';
        
        // Get video usage info
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        fetch(`/admin/videos/${videoId}/usage-info`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.used_in_modules > 0) {
                // Show warning
                const warningDiv = document.getElementById('videoUsageWarning');
                const warningText = document.getElementById('warningMessageText');
                const modulesList = document.getElementById('modulesList');
                
                warningText.textContent = `هذا الفيديو مرتبط بـ ${data.used_in_modules} وحدة دراسية. سيتم إزالة الفيديو من هذه الوحدات عند الحذف.`;
                
                // Show modules list
                if (data.modules && data.modules.length > 0) {
                    let modulesHtml = '<small class="d-block mb-1"><strong>الوحدات المرتبطة:</strong></small><ul class="mb-0 small" style="text-align: right;">';
                    data.modules.forEach(module => {
                        modulesHtml += `<li>${module.course} - ${module.section} - ${module.title}</li>`;
                    });
                    modulesHtml += '</ul>';
                    modulesList.innerHTML = modulesHtml;
                }
                
                warningDiv.classList.remove('d-none');
                document.getElementById('deleteVideoMessage').textContent = 'هل أنت متأكد من حذف هذا الفيديو؟ سيتم إزالة الفيديو من جميع الوحدات المرتبطة به.';
            }
        })
        .catch(error => {
            console.error('Error fetching usage info:', error);
            // Continue anyway - don't block the modal from showing
        });
        
        const modal = new bootstrap.Modal(document.getElementById('deleteVideoModal'));
        modal.show();
    }

    // Show Alert Modal
    function showAlert(type, message) {
        const modal = new bootstrap.Modal(document.getElementById('alertModal'));
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const label = document.getElementById('alertModalLabel');
        const messageEl = document.getElementById('alertMessage');
        
        if (type === 'success') {
            iconContainer.className = 'avatar avatar-xl bg-success-transparent mx-auto mb-3';
            icon.className = 'fas fa-check-circle fs-24 text-success';
            label.textContent = 'نجح';
        } else {
            iconContainer.className = 'avatar avatar-xl bg-danger-transparent mx-auto mb-3';
            icon.className = 'fas fa-exclamation-circle fs-24 text-danger';
            label.textContent = 'خطأ';
        }
        
        messageEl.textContent = message;
        modal.show();
    }

    // Confirm Toggle Publish
    document.getElementById('confirmTogglePublish').addEventListener('click', function() {
        if (!currentVideoId) {
            console.error('No video ID selected');
            return;
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('togglePublishModal'));
        modal.hide();
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        console.log('Toggling publish for video:', currentVideoId);
        console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
        
        fetch(`/admin/videos/${currentVideoId}/toggle-publish`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        })
        .then(response => {
            // Check if response is ok
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || 'حدث خطأ في الخادم');
                    } catch (e) {
                        if (e.message) throw e;
                        throw new Error('حدث خطأ في الخادم: ' + response.status);
                    }
                });
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, try to parse as JSON anyway
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        // If not JSON, reload page
                        location.reload();
                        return null;
                    }
                });
            }
        })
        .then(data => {
            if (data) {
                if (data.success) {
                    showAlert('success', data.message || 'تم تغيير حالة النشر بنجاح');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message || 'حدث خطأ أثناء تغيير حالة النشر');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', error.message || 'حدث خطأ في الاتصال');
        });
    });

    // Confirm Delete Video
    document.getElementById('confirmDeleteVideo').addEventListener('click', function() {
        if (!currentVideoId) {
            console.error('No video ID selected');
            return;
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteVideoModal'));
        modal.hide();
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        console.log('Deleting video:', currentVideoId);
        console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
        
        fetch(`/admin/videos/${currentVideoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            // Check if response is ok
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || 'حدث خطأ في الخادم');
                    } catch (e) {
                        if (e.message) throw e;
                        throw new Error('حدث خطأ في الخادم: ' + response.status);
                    }
                });
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, try to parse as JSON anyway
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        // If not JSON, reload page
                        location.reload();
                        return null;
                    }
                });
            }
        })
        .then(data => {
            if (data) {
                if (data.success) {
                    let message = data.message || 'تم حذف الفيديو بنجاح';
                    if (data.warning) {
                        message += '\n\n' + data.warning;
                    }
                    showAlert('success', message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('error', data.message || 'حدث خطأ أثناء الحذف');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', error.message || 'حدث خطأ أثناء الحذف');
        });
    });
</script>
@stop
