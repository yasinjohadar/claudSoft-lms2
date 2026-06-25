@extends('admin.layouts.master')

@section('page-title')
    فرق التحدي — {{ $challenge->title }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">فرق: {{ $challenge->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.project-challenges.index') }}">تحديات المشاريع</a></li>
                            <li class="breadcrumb-item active">الفرق</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                        <i class="fe fe-plus me-1"></i>إضافة فريق
                    </button>
                    <a href="{{ route('admin.project-challenges.manage-stages', $challenge->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fe fe-layers me-1"></i>المراحل
                    </a>
                    <a href="{{ route('admin.project-challenges.edit', $challenge->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fe fe-arrow-right me-1"></i>العودة للتحدي
                    </a>
                </div>
            </div>

            @if($pendingJoinRequests->isNotEmpty())
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fe fe-user-plus me-1"></i>طلبات انضمام معلقة
                            <span class="badge bg-warning ms-1">{{ $pendingJoinRequests->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>الفريق</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingJoinRequests as $request)
                                        <tr>
                                            <td>{{ $request->user->name ?? $request->user->email }}</td>
                                            <td>{{ $request->team->name }}</td>
                                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <form action="{{ route('admin.project-challenges.approve-join-request', [$challenge->id, $request->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fe fe-check me-1"></i>قبول
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.project-challenges.reject-join-request', [$challenge->id, $request->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" name="reject_reason" class="form-control" placeholder="سبب الرفض (اختياري)">
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fe fe-x"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الفرق ({{ $teams->total() }})</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>اسم الفريق</th>
                                    <th>القائد</th>
                                    <th>الأعضاء</th>
                                    <th>التقدم</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teams as $team)
                                    <tr>
                                        <td>
                                            <strong>{{ $team->name }}</strong>
                                            @if($team->description)
                                                <br><small class="text-muted">{{ Str::limit($team->description, 60) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $team->leader->name ?? '—' }}</td>
                                        <td>{{ $team->activeMembers->count() }} / {{ $challenge->max_members }}</td>
                                        <td>
                                            <div class="pc-progress" style="width:120px">
                                                <div class="pc-progress__bar" style="width:{{ min(100, (float)$team->progress_percent) }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ number_format($team->progress_percent, 0) }}%</small>
                                        </td>
                                        <td>
                                            @if($team->isActive())
                                                <span class="badge bg-success">نشط</span>
                                            @elseif($team->isPending())
                                                <span class="badge bg-warning">معلق</span>
                                            @elseif($team->isCompleted())
                                                <span class="badge bg-primary">مكتمل</span>
                                            @else
                                                <span class="badge bg-danger">مستبعد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <a href="{{ route('admin.project-challenges.teams.show', [$challenge->id, $team->id]) }}" class="btn btn-sm btn-primary">
                                                    <i class="fe fe-settings me-1"></i>إدارة
                                                </a>
                                                @if($team->isPending())
                                                    <form action="{{ route('admin.project-challenges.activate-team', [$challenge->id, $team->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('تفعيل هذا الفريق؟')">
                                                            <i class="fe fe-play me-1"></i>تفعيل
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد فرق بعد</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($teams->hasPages())
                    <div class="card-footer">{{ $teams->links() }}</div>
                @endif
            </div>

            <div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('admin.project-challenges.teams.store', $challenge->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="createTeamModalLabel">إضافة فريق جديد</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">اسم الفريق <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">قائد الفريق <span class="text-danger">*</span></label>
                                    <select name="leader_id" id="create_team_leader_id" class="form-select pc-student-select" required data-placeholder="ابحث عن طالب">
                                        @if(old('leader_id'))
                                            <option value="{{ old('leader_id') }}" selected>—</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">الحالة</label>
                                    <select name="status" class="form-select">
                                        <option value="active" @selected(old('status', 'active') === 'active')>نشط</option>
                                        <option value="pending" @selected(old('status') === 'pending')>معلق</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-plus me-1"></i>إنشاء الفريق
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            const searchUrl = @json(route('admin.project-challenges.search-students'));
            const modal = document.getElementById('createTeamModal');

            const initSelect = () => {
                const $el = $('#create_team_leader_id');
                if (!$el.length || $el.hasClass('select2-hidden-accessible')) return;

                $el.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dir: 'rtl',
                    dropdownParent: $('#createTeamModal'),
                    placeholder: 'ابحث عن طالب',
                    allowClear: true,
                    minimumInputLength: 2,
                    ajax: {
                        url: searchUrl,
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({ q: params.term || '' }),
                        processResults: (data) => ({ results: data.results || [] }),
                    },
                });
            };

            if (modal) {
                modal.addEventListener('shown.bs.modal', initSelect);
            }

            @if($errors->any() && old('name'))
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createTeamModal')).show();
                initSelect();
            @endif
        })();
    </script>
@stop
