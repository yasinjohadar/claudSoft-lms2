@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'تفاصيل البث #'.$broadcast->id;
    $tgTitle = 'تفاصيل البث #'.$broadcast->id;
    $tgSubtitle = $broadcast->sent_count.' مرسل من '.$broadcast->total_recipients;
    $breadcrumb = 'تفاصيل البث';
@endphp

@section('tg-content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card admin-stats-card admin-stats-card--green border-0 shadow-sm">
            <div class="card-body text-center">
                <p class="admin-stats-card__label">مرسل</p>
                <h2 class="text-success mb-0">{{ $broadcast->sent_count }}</h2>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card admin-stats-card admin-stats-card--orange border-0 shadow-sm">
            <div class="card-body text-center">
                <p class="admin-stats-card__label">فاشل</p>
                <h2 class="text-danger mb-0">{{ $broadcast->failed_count }}</h2>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card admin-stats-card admin-stats-card--cyan border-0 shadow-sm">
            <div class="card-body text-center">
                <p class="admin-stats-card__label">الحالة</p>
                <h2 class="mb-0 fs-4">{{ $broadcast->status }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="tg-form-section mt-2">
    <div class="tg-form-section__title">
        <span class="tg-form-section__icon"><i class="ri-file-text-line"></i></span>
        نص الرسالة
    </div>
    <pre class="bg-light p-3 rounded mb-0" style="white-space: pre-wrap;">{{ $broadcast->message_template }}</pre>
</div>

<div class="tg-form-section">
    <div class="tg-form-section__title">
        <span class="tg-form-section__icon"><i class="ri-user-line"></i></span>
        المستلمون
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>الطالب</th><th>الحالة</th><th>خطأ</th></tr></thead>
            <tbody>
            @foreach($broadcast->recipients as $r)
                <tr>
                    <td>{{ $r->user?->name }}</td>
                    <td><span class="badge bg-{{ $r->status === 'sent' ? 'success' : ($r->status === 'failed' ? 'danger' : 'secondary') }}-transparent">{{ $r->status }}</span></td>
                    <td class="small text-danger">{{ $r->error_message }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
