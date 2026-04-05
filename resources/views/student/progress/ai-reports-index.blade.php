@extends('student.layouts.master')

@section('page-title')
    تقارير الدراسة — {{ $course->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقارير الدراسة (AI) — {{ $course->title }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.study-reports.index') }}">تقارير الدراسة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.progress.show', $course->id) }}">تقدم الكورس</a></li>
                        <li class="breadcrumb-item active">{{ $course->title }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="alert alert-info">
            النتائج الرقمية مستمدة من سجلات المنصة؛ النص التفسيري مولَّد آلياً وقد يحتاج مراجعة من المدرّس.
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $r)
                                <tr>
                                    <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('student.progress.ai-reports.show', $r) }}" class="btn btn-sm btn-primary">عرض</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">لا توجد تقارير بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>
@stop
