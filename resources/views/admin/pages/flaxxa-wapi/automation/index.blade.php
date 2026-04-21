@extends('admin.layouts.master')

@section('page-title')
    أتمتة Flaxxa — قواعد الأحداث
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">أتمتة الإرسال حسب الحدث</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.messages.index') }}">Flaxxa</a></li>
                        <li class="breadcrumb-item active">الأتمتة</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.flaxxa-wapi.automation.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> قاعدة جديدة</a>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="alert alert-secondary border-0 small" role="alert">
            <strong>المرسل الافتراضي لـ Flaxxa</strong> هو الجلسة المرتبطة بـ <code>WHATSAPP_TOKEN</code> أو التوكن المحفوظ في إعدادات Flaxxa (حساب واتساب واحد لكل توكن).
        </div>

        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الحدث</th>
                                <th>القالب</th>
                                <th>تقييد</th>
                                <th>حالة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $r)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $eventLabels[$r->event_key] ?? $r->event_key }}</div>
                                        <code class="small">{{ $r->event_key }}</code>
                                    </td>
                                    <td>{{ $r->effectiveTemplateName() ?? '—' }} <span class="text-muted">/ {{ $r->effectiveLanguage() ?? '—' }}</span></td>
                                    <td class="small">
                                        @if($r->course_id) كورس #{{ $r->course_id }} @endif
                                        @if($r->group_id) مجموعة #{{ $r->group_id }} @endif
                                        @if($r->lesson_id) درس #{{ $r->lesson_id }} @endif
                                        @if(!$r->course_id && !$r->group_id && !$r->lesson_id && !$r->module_id) <span class="text-muted">عام</span> @endif
                                    </td>
                                    <td>
                                        @if($r->is_active)
                                            <span class="badge bg-success">مفعّل</span>
                                        @else
                                            <span class="badge bg-secondary">موقوف</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.flaxxa-wapi.automation.edit', $r) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                        <form action="{{ route('admin.flaxxa-wapi.automation.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف القاعدة؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">لا قواعد بعد. أنشئ قاعدة واربطها بقالب معتمد في Meta.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($rules->hasPages())
                <div class="card-footer">{{ $rules->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
