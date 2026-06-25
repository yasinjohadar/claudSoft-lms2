@extends('admin.layouts.master')

@section('page-title')
    التحديات البرمجية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">التحديات البرمجية</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">التحديات البرمجية</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('admin.challenge-grading.index') }}" class="btn btn-warning">
                        <i class="fe fe-check-square me-1"></i>تقييم التسليمات
                    </a>
                    <a href="{{ route('programming-challenges.create') }}" class="btn btn-primary">
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
                                    <th>التقييم</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($challenges as $challenge)
                                    <tr>
                                        <td>
                                            <strong>{{ $challenge->title }}</strong>
                                            @if($challenge->is_standalone)
                                                <span class="badge bg-info-transparent ms-1">مكتبة</span>
                                            @endif
                                        </td>
                                        <td>{{ $challenge->challenge_type === 'web_sandbox' ? 'ويب' : 'تنفيذ كود' }}</td>
                                        <td>
                                            @php
                                                $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
                                            @endphp
                                            {{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
                                        </td>
                                        <td>{{ $challenge->grading_mode === 'manual' ? 'يدوي' : ($challenge->grading_mode === 'auto' ? 'آلي' : 'هجين') }}</td>
                                        <td>
                                            @if($challenge->is_published)
                                                <span class="badge bg-success">منشور</span>
                                            @else
                                                <span class="badge bg-secondary">مسودة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}" class="btn btn-outline-primary" title="اللغات">لغات</a>
                                                <a href="{{ route('programming-challenges.manage-starter', $challenge->id) }}" class="btn btn-outline-info" title="الكود الابتدائي">كود</a>
                                                <a href="{{ route('programming-challenges.edit', $challenge->id) }}" class="btn btn-outline-secondary" title="تعديل"><i class="fe fe-edit-2"></i></a>
                                                <form action="{{ route('programming-challenges.destroy', $challenge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف التحدي؟')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد تحديات بعد</td></tr>
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
