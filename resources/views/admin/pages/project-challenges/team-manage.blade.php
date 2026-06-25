@extends('admin.layouts.master')

@section('page-title')
    إدارة الفريق — {{ $team->name }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@php
    $roleLabels = [
        'leader' => 'قائد',
        'backend' => 'مطور Backend',
        'frontend' => 'مطور Frontend',
        'designer' => 'مصمم',
        'devops' => 'DevOps',
        'qa' => 'اختبار QA',
        'member' => 'عضو',
    ];
    $submissionStatusLabels = [
        'draft' => 'مسودة',
        'submitted' => 'مُسلّم',
        'under_review' => 'قيد المراجعة',
        'approved' => 'معتمد',
        'resubmit_required' => 'يحتاج إعادة تسليم',
        'rejected' => 'مرفوض',
    ];
@endphp

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الفريق: {{ $team->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.project-challenges.index') }}">تحديات المشاريع</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.project-challenges.manage-teams', $challenge->id) }}">الفرق</a></li>
                            <li class="breadcrumb-item active">{{ $team->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.project-challenges.manage-teams', $challenge->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fe fe-arrow-right me-1"></i>العودة للفرق
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">بيانات الفريق</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.project-challenges.teams.update', [$challenge->id, $team->id]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label">اسم الفريق</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $team->name) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $team->description) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">القائد</label>
                                    <select name="leader_id" id="leader_id" class="form-select pc-student-select" data-placeholder="ابحث عن قائد الفريق">
                                        @if($team->leader)
                                            <option value="{{ $team->leader_id }}" selected>{{ $team->leader->name_ar ?: $team->leader->name }} ({{ $team->leader->email }})</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">الحالة</label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending" @selected(old('status', $team->status) === 'pending')>معلق</option>
                                        <option value="active" @selected(old('status', $team->status) === 'active')>نشط</option>
                                        <option value="completed" @selected(old('status', $team->status) === 'completed')>مكتمل</option>
                                        <option value="disqualified" @selected(old('status', $team->status) === 'disqualified')>مستبعد</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i>حفظ التغييرات
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">الأعضاء ({{ $team->activeMembers->count() }} / {{ $challenge->max_members }})</div>
                        </div>
                        <div class="card-body border-bottom">
                            <form action="{{ route('admin.project-challenges.teams.members.store', [$challenge->id, $team->id]) }}" method="POST" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label">إضافة عضو</label>
                                    <select name="user_id" id="member_user_id" class="form-select pc-student-select" required data-placeholder="ابحث عن طالب">
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">الدور</label>
                                    <select name="role" class="form-select" required>
                                        @foreach($teamRoles as $roleKey => $roleName)
                                            @if($roleKey !== 'leader')
                                                <option value="{{ $roleKey }}">{{ $roleLabels[$roleKey] ?? $roleName }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100" @disabled(!$team->canAcceptMembers())>
                                        <i class="fe fe-user-plus"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>الطالب</th>
                                            <th>الدور</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($team->activeMembers as $member)
                                            <tr>
                                                <td>
                                                    {{ $member->user->name_ar ?? $member->user->name ?? $member->user->email }}
                                                    @if($team->leader_id === $member->user_id)
                                                        <span class="badge bg-primary ms-1">قائد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <form action="{{ route('admin.project-challenges.teams.members.update-role', [$challenge->id, $team->id, $member->user_id]) }}" method="POST" class="d-flex gap-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            @foreach($teamRoles as $roleKey => $roleName)
                                                                <option value="{{ $roleKey }}" @selected($member->role === $roleKey || ($team->leader_id === $member->user_id && $roleKey === 'leader'))>
                                                                    {{ $roleLabels[$roleKey] ?? $roleName }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                </td>
                                                <td>
                                                    <form action="{{ route('admin.project-challenges.teams.members.destroy', [$challenge->id, $team->id, $member->user_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('إزالة هذا العضو من الفريق؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-3">لا يوجد أعضاء</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">مراحل المشروع والمهام</div>
                            <a href="{{ route('admin.project-challenges.manage-stages', $challenge->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fe fe-layers me-1"></i>إدارة المراحل
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>المرحلة</th>
                                            <th>حالة المرحلة</th>
                                            <th>تسليم الفريق</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stages as $stage)
                                            @php
                                                $submission = $submissionsByStage->get($stage->id);
                                                $isUnlocked = $team->hasAdminUnlockedStage($stage->id) || app(\App\Services\ProjectChallenge\ProjectSubmissionService::class)->isStageUnlockedForTeam($team, $stage);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $stage->title }}</strong>
                                                    @if($stage->description)
                                                        <br><small class="text-muted">{{ Str::limit($stage->description, 80) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($stage->isOpen())
                                                        <span class="badge bg-success">مفتوحة</span>
                                                    @elseif($stage->status === 'closed')
                                                        <span class="badge bg-secondary">مغلقة</span>
                                                    @else
                                                        <span class="badge bg-warning">مقفلة</span>
                                                    @endif
                                                    @if($team->hasAdminUnlockedStage($stage->id))
                                                        <span class="badge bg-info ms-1">فتح إداري</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($submission)
                                                        <span class="badge bg-light text-dark">{{ $submissionStatusLabels[$submission->status] ?? $submission->status }}</span>
                                                        @if($submission->score !== null)
                                                            <small class="text-muted d-block">{{ $submission->score }} / {{ $submission->max_score }}</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        @if(!$isUnlocked)
                                                            <form action="{{ route('admin.project-challenges.teams.stages.unlock', [$challenge->id, $team->id, $stage->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-warning" title="فتح المرحلة لهذا الفريق">
                                                                    <i class="fe fe-unlock me-1"></i>فتح
                                                                </button>
                                                            </form>
                                                        @endif
                                                        @if($submission && in_array($submission->status, ['submitted', 'under_review', 'resubmit_required']))
                                                            <a href="{{ route('admin.project-grading.show', $submission->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="fe fe-check-square me-1"></i>تصحيح
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

            $('.pc-student-select').each(function () {
                const $el = $(this);
                $el.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dir: 'rtl',
                    placeholder: $el.data('placeholder') || 'ابحث عن طالب',
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
            });
        })();
    </script>
@stop
