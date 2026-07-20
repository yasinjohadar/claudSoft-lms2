@extends('admin.layouts.master')

@section('page-title')
    مكتبات Bunny Stream
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">مكتبات Bunny Stream</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('videos.index') }}">الفيديوهات</a></li>
                            <li class="breadcrumb-item active">مكتبات Bunny</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                    <form action="{{ route('bunny-stream-libraries.sync-videos') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary" onclick="return confirm('سيتم ربط جميع فيديوهات Bunny بالمكتبات المسجّلة تلقائياً. متابعة؟')">
                            <i class="fas fa-link me-2"></i>ربط الفيديوهات تلقائياً
                        </button>
                    </form>
                    <a href="{{ route('bunny-stream-libraries.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة مكتبة
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-database fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">عدد المكتبات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $libraries->count() }}</h4>
                                    <span class="badge bg-primary-transparent">مكتبة مسجّلة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-video fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">فيديوهات Bunny</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $bunnyVideoCount }}</h4>
                                    <span class="badge bg-info-transparent">فيديو</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-6 col-md-6">
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
                                        <p class="fw-semibold mb-1">فيديوهات مربوطة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $linkedCount }}</h4>
                                    @if($bunnyVideoCount > 0)
                                        <span class="badge bg-{{ $linkedCount >= $bunnyVideoCount ? 'success' : 'warning' }}-transparent">
                                            {{ round(($linkedCount / $bunnyVideoCount) * 100) }}%
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-transparent">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($linkedCount < $bunnyVideoCount)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    يوجد <strong>{{ $bunnyVideoCount - $linkedCount }}</strong> فيديو Bunny غير مربوط بمكتبة.
                    أضف المكتبات المطلوبة ثم اضغط <strong>«ربط الفيديوهات تلقائياً»</strong>.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Libraries Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة المكتبات</div>
                </div>
                <div class="card-body">
                    @if($libraries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>المكتبة</th>
                                        <th>Library ID</th>
                                        <th>الفيديوهات</th>
                                        <th>المفتاح</th>
                                        <th>API Key</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($libraries as $library)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-primary-transparent me-2">
                                                        <i class="fas fa-layer-group"></i>
                                                    </span>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ $library->library_name }}</h6>
                                                        <small class="text-muted">{{ $library->created_at?->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-transparent fs-13">{{ $library->library_id }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-transparent">
                                                    <i class="fas fa-video me-1"></i>{{ $library->videos_count }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($library->hasTokenSecurityKey())
                                                    <span class="badge bg-success-transparent">
                                                        <i class="fas fa-lock me-1"></i>مُعد
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-transparent">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>ناقص
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($library->api_key)
                                                    <span class="badge bg-info-transparent">
                                                        <i class="fas fa-key me-1"></i>مُعد
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($library->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">معطّل</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('bunny-stream-libraries.edit', $library) }}"
                                                       class="btn btn-sm btn-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="confirmDelete({{ $library->id }}, '{{ $library->library_name }}', {{ $library->videos_count }})"
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
                    @else
                        <div class="text-center py-5">
                            <span class="avatar avatar-xl bg-primary-transparent mx-auto mb-3">
                                <i class="fas fa-database fs-24"></i>
                            </span>
                            <h6 class="text-muted mb-3">لا توجد مكتبات Bunny مسجّلة بعد</h6>
                            <p class="text-muted mb-3">أضف مكتبات Bunny Stream لتفعيل التوقيع الآمن على الفيديوهات</p>
                            <a href="{{ route('bunny-stream-libraries.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>إضافة أول مكتبة
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-trash-alt fa-3x"></i>
                        </span>
                    </div>
                    <h5 class="modal-title text-center mb-3 fw-bold" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                        تأكيد حذف المكتبة
                    </h5>
                    <p class="text-muted mb-3" id="deleteMessage">هل أنت متأكد من حذف هذه المكتبة؟</p>
                    <div id="deleteWarning" class="alert alert-warning text-start d-none mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="deleteWarningText"></span>
                    </div>
                    <p class="text-danger small mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        لن يمكن التراجع عن هذه العملية.
                    </p>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </button>
                        <form id="deleteForm" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-trash-alt me-2"></i>حذف نهائياً
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    function confirmDelete(id, name, videosCount) {
        const form = document.getElementById('deleteForm');
        form.action = @json(route('bunny-stream-libraries.destroy', ['bunny_stream_library' => '__ID__'])).replace('__ID__', id);

        document.getElementById('deleteMessage').textContent = `هل أنت متأكد من حذف مكتبة "${name}"؟`;

        const warning = document.getElementById('deleteWarning');
        if (videosCount > 0) {
            document.getElementById('deleteWarningText').textContent = `هذه المكتبة مرتبطة بـ ${videosCount} فيديو. لا يمكن حذفها إلا بعد إلغاء ربطها أو تعطيلها بدلاً من الحذف.`;
            warning.classList.remove('d-none');
        } else {
            warning.classList.add('d-none');
        }

        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@stop
