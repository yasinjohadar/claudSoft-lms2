@extends('admin.layouts.master')

@section('page-title')
    حالات الاختبار — {{ $challenge->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-4">
                <h5 class="page-title fs-21 mb-1">حالات الاختبار — {{ $challenge->title }}</h5>
                <p class="text-muted">الخطوة 3 من 3 (للتقييم الآلي في المرحلة 2)</p>
            </div>

            <form action="{{ route('programming-challenges.update-test-cases', $challenge->id) }}" method="POST" id="test-cases-form">
                @csrf @method('PUT')
                <div class="card custom-card">
                    <div class="card-body" id="test-cases-container">
                        @forelse($challenge->testCases as $i => $tc)
                            <div class="border rounded p-3 mb-3 test-case-row">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">المدخل</label>
                                        <textarea name="test_cases[{{ $i }}][input]" class="form-control font-monospace" rows="3" dir="ltr">{{ $tc->input }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">المخرج المتوقع</label>
                                        <textarea name="test_cases[{{ $i }}][expected_output]" class="form-control font-monospace" rows="3" dir="ltr">{{ $tc->expected_output }}</textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">النقاط</label>
                                        <input type="number" name="test_cases[{{ $i }}][points]" class="form-control" value="{{ $tc->points }}" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">المهلة (ms)</label>
                                        <input type="number" name="test_cases[{{ $i }}][timeout_ms]" class="form-control" value="{{ $tc->timeout_ms }}" min="100">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="form-check">
                                            <input type="checkbox" name="test_cases[{{ $i }}][is_hidden]" value="1" class="form-check-input" @checked($tc->is_hidden)>
                                            <label class="form-check-label">مخفي</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted" id="empty-msg">لا توجد حالات اختبار. أضف واحدة أدناه.</p>
                        @endforelse
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="add-test-case">+ إضافة حالة</button>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('programming-challenges.manage-starter', $challenge->id) }}" class="btn btn-light">رجوع</a>
                            <button type="submit" class="btn btn-primary">حفظ وإنهاء</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let tcIndex = {{ $challenge->testCases->count() }};
        document.getElementById('add-test-case')?.addEventListener('click', function() {
            document.getElementById('empty-msg')?.remove();
            const html = `<div class="border rounded p-3 mb-3 test-case-row">
                <div class="row">
                    <div class="col-md-6 mb-2"><label class="form-label">المدخل</label>
                    <textarea name="test_cases[${tcIndex}][input]" class="form-control font-monospace" rows="3" dir="ltr"></textarea></div>
                    <div class="col-md-6 mb-2"><label class="form-label">المخرج المتوقع</label>
                    <textarea name="test_cases[${tcIndex}][expected_output]" class="form-control font-monospace" rows="3" dir="ltr"></textarea></div>
                    <div class="col-md-3"><label class="form-label">النقاط</label>
                    <input type="number" name="test_cases[${tcIndex}][points]" class="form-control" value="1" step="0.01"></div>
                    <div class="col-md-3"><label class="form-label">المهلة (ms)</label>
                    <input type="number" name="test_cases[${tcIndex}][timeout_ms]" class="form-control" value="5000"></div>
                    <div class="col-md-3 d-flex align-items-end"><div class="form-check">
                    <input type="checkbox" name="test_cases[${tcIndex}][is_hidden]" value="1" class="form-check-input" checked>
                    <label class="form-check-label">مخفي</label></div></div>
                </div></div>`;
            document.getElementById('test-cases-container').insertAdjacentHTML('beforeend', html);
            tcIndex++;
        });
    </script>
    @endpush
@stop
