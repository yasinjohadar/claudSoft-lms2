@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'بث جماعي — Telegram';
    $tgTitle = 'إرسال جماعي للطلاب';
    $tgSubtitle = 'يُرسل فقط للطلاب الذين ربطوا حساب Telegram عبر البوت. يُطبَّق فاصل زمني تلقائي بين الرسائل.';
    $breadcrumb = 'بث جماعي';
@endphp

@section('tg-content')
<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.telegram.broadcast.store') }}" id="tgBroadcastForm">
            @csrf

            <div class="tg-form-section">
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-group-line"></i></span>
                    الجمهور المستهدف
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="course_id">الدورة</label>
                        <select name="course_id" id="course_id" class="form-select">
                            <option value="">— الكل —</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="group_id">مجموعة التسجيل (ID)</label>
                        <input type="number" name="group_id" id="group_id" class="form-control"
                               value="{{ old('group_id') }}" placeholder="مثال: 28">
                        <small class="text-muted">عند التحديد يُرسل لأعضاء المجموعة فقط.</small>
                    </div>
                </div>
            </div>

            <div class="tg-form-section">
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-message-2-line"></i></span>
                    محتوى الرسالة
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">قالب جاهز (اختياري)</label>
                    <select name="telegram_template_id" class="form-select">
                        <option value="">— كتابة يدوية —</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}" @selected(old('telegram_template_id') == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">نص الرسالة <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control" rows="7" required
                              placeholder="مرحباً {student_name}&#10;{course_name} — {group_name}">{{ old('message') }}</textarea>
                    <small class="text-muted d-block mt-2">
                        المتغيرات: <code>{student_name}</code> <code>{course_name}</code> <code>{group_name}</code> <code>{group_link}</code>
                    </small>
                </div>
            </div>

            <button type="submit" class="btn btn-lg text-white px-4" style="background: linear-gradient(135deg, #229ED9, #0088cc);">
                <i class="ri-send-plane-fill me-2"></i>بدء البث الجماعي
            </button>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card admin-stats-card admin-stats-card--cyan mb-3 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="admin-stats-card__icon-wrap">
                    <i class="ri-user-follow-line admin-stats-card__icon"></i>
                </div>
                <div>
                    <p class="admin-stats-card__label mb-1">طلاب مؤهلون للبث</p>
                    <h3 class="admin-stats-card__value mb-0 fs-2" id="eligibleCount">0</h3>
                    <p class="text-muted small mb-0">لديهم Telegram مربوط</p>
                </div>
            </div>
        </div>

        <div class="tg-guide-box">
            <h6 class="fw-bold mb-3"><i class="ri-lightbulb-line me-1 text-info"></i> نصائح</h6>
            <ul class="small mb-0">
                <li>الطالب يجب أن يكون قد ضغط <strong>Start</strong> على البوت.</li>
                <li>يُطبَّق تأخير بين الرسائل لتقليل مخاطر الحظر.</li>
                <li>الرسالة الأولى تُرسل فوراً؛ الباقي عبر الطابور.</li>
                <li>راجع التقرير من تبويب «تقارير البث».</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('tg-scripts')
<script>
async function refreshCount() {
    const params = new URLSearchParams({
        course_id: document.getElementById('course_id').value,
        group_id: document.getElementById('group_id').value,
    });
    try {
        const r = await fetch(@json(route('admin.telegram.broadcast.students-count')) + '?' + params);
        const j = await r.json();
        document.getElementById('eligibleCount').textContent = j.count ?? 0;
    } catch (e) {
        document.getElementById('eligibleCount').textContent = '—';
    }
}
document.getElementById('course_id')?.addEventListener('change', refreshCount);
document.getElementById('group_id')?.addEventListener('input', refreshCount);
refreshCount();
</script>
@endsection
