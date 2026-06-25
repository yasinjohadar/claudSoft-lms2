@extends('admin.layouts.master')

@section('page-title')
    تحديات المشاريع
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تحديات المشاريع</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">تحديات المشاريع</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('admin.project-grading.index') }}" class="btn btn-warning">
                        <i class="fe fe-check-square me-1"></i>تقييم التسليمات
                    </a>
                    <a href="{{ route('admin.project-challenges.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-1"></i>تحدي جديد
                    </a>
                </div>
            </div>

            <div class="card custom-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>النوع</th>
                                    <th>الصعوبة</th>
                                    <th>الفرق</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $typeLabels = [
                                        'team_project' => 'مشروع فريق',
                                        'open_challenge' => 'تحدي مفتوح',
                                        'hackathon' => 'هاكاثون',
                                        'capstone' => 'مشروع تخرج',
                                    ];
                                    $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
                                @endphp
                                @forelse($challenges as $challenge)
                                    <tr>
                                        <td>
                                            <strong>{{ $challenge->title }}</strong>
                                            @if($challenge->is_featured)
                                                <span class="badge bg-warning-transparent ms-1">مميز</span>
                                            @endif
                                        </td>
                                        <td>{{ $typeLabels[$challenge->project_type] ?? $challenge->project_type }}</td>
                                        <td>
                                            <span class="pc-tag pc-tag--{{ $challenge->difficulty }}">
                                                {{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
                                            </span>
                                        </td>
                                        <td>{{ $challenge->teams_count }}</td>
                                        <td>
                                            @if($challenge->isPublished())
                                                <span class="badge bg-success">منشور</span>
                                            @elseif($challenge->isDraft())
                                                <span class="badge bg-secondary">مسودة</span>
                                            @elseif($challenge->isArchived())
                                                <span class="badge bg-dark">مؤرشف</span>
                                            @else
                                                <span class="badge bg-danger">مغلق</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm flex-wrap">
                                                <a href="{{ route('admin.project-challenges.edit', $challenge->id) }}" class="btn btn-outline-secondary" title="تعديل">
                                                    <i class="fe fe-edit-2"></i>
                                                </a>
                                                <a href="{{ route('admin.project-challenges.manage-stages', $challenge->id) }}" class="btn btn-outline-primary" title="المراحل">مراحل</a>
                                                <a href="{{ route('admin.project-challenges.manage-teams', $challenge->id) }}" class="btn btn-outline-info" title="الفرق">فرق</a>
                                                @if($challenge->isDraft())
                                                    <form action="{{ route('admin.project-challenges.publish', $challenge->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="نشر" onclick="return confirm('نشر هذا التحدي؟')">
                                                            <i class="fe fe-upload"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('admin.project-grading.index') }}" class="btn btn-outline-warning" title="التقييم">
                                                    <i class="fe fe-check-square"></i>
                                                </a>
                                                <form action="{{ route('admin.project-challenges.destroy', $challenge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف التحدي؟')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد تحديات مشاريع بعد</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($challenges->hasPages())
                    <div class="card-footer">{{ $challenges->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@stop
