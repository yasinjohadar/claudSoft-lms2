@extends('admin.layouts.master')

@section('page-title')
    تصحيح الأسئلة المقالية بالذكاء الاصطناعي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تصحيح الأسئلة المقالية</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الذكاء الاصطناعي</li>
                            <li class="breadcrumb-item active">تصحيح المقالي</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.ai.essay-grading') }}" class="row g-3">
                                <div class="col-md-4">
                                    <select name="status" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="ungraded" {{ request('status') == 'ungraded' ? 'selected' : '' }}>غير مصححة</option>
                                        <option value="ai_graded" {{ request('status') == 'ai_graded' ? 'selected' : '' }}>مصححة بالذكاء الاصطناعي</option>
                                        <option value="manually_graded" {{ request('status') == 'manually_graded' ? 'selected' : '' }}>مصححة يدوياً</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">تصفية</button>
                                    <a href="{{ route('admin.ai.essay-grading') }}" class="btn btn-light">إعادة تعيين</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responses Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الإجابات المقالية</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>الطالب</th>
                                            <th>السؤال</th>
                                            <th>الإجابة</th>
                                            <th>الدرجة</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($responses as $response)
                                            <tr>
                                                <td>{{ $response->user->name ?? 'N/A' }}</td>
                                                <td>{{ Str::limit($response->question->question_text ?? 'N/A', 50) }}</td>
                                                <td>{{ Str::limit($response->response_text ?? 'N/A', 100) }}</td>
                                                <td>
                                                    @if($response->score_obtained)
                                                        {{ $response->score_obtained }}/{{ $response->max_score }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($response->ai_graded)
                                                        <span class="badge bg-info">مصححة بالذكاء الاصطناعي</span>
                                                    @elseif($response->score_obtained)
                                                        <span class="badge bg-success">مصححة يدوياً</span>
                                                    @else
                                                        <span class="badge bg-warning">غير مصححة</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-list">
                                                        @if(!$response->score_obtained)
                                                            <button type="button" class="btn btn-sm btn-primary grade-essay" data-id="{{ $response->id }}">
                                                                <i class="fas fa-magic me-1"></i>تصحيح تلقائي
                                                            </button>
                                                        @endif
                                                        <a href="#" class="btn btn-sm btn-info view-response" data-id="{{ $response->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">لا توجد إجابات مقالية</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $responses->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.grade-essay').forEach(btn => {
            btn.addEventListener('click', function() {
                const responseId = this.dataset.id;
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري التصحيح...';

                fetch(`/admin/ai/essay-grading/${responseId}/grade`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ async: false })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✓ تم التصحيح بنجاح!\nالدرجة: ' + data.grading.total_score + '/' + data.grading.max_score);
                        location.reload();
                    } else {
                        alert('✗ فشل التصحيح: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('حدث خطأ: ' + error.message);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-magic me-1"></i>تصحيح تلقائي';
                });
            });
        });
    </script>
    @endpush
@stop

