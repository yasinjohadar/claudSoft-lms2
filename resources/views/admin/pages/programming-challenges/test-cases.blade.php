@extends('admin.layouts.master')

@section('page-title')
    حالات الاختبار — {{ $challenge->title }}
@stop

@push('styles')
    @include('admin.pages.programming-challenges.partials.form-styles')
    <style>
        .pch-langs__steps {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .pch-langs__step {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #64748b;
        }

        .pch-langs__step.is-active {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .pch-langs__step.is-done {
            background: #dcfce7;
            color: #15803d;
        }

        .pch-tc__footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border-top: 1px solid var(--pf-border, #e2e8f0);
            background: var(--pf-soft, #f8fafc);
        }

        .pch-tc__explain {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 1.15rem;
        }

        .pch-tc__card-info {
            padding: 1rem 1.1rem;
            border-radius: 14px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .pch-tc__card-info h6 {
            margin: 0 0 0.4rem;
            font-size: 0.92rem;
            font-weight: 800;
        }

        .pch-tc__card-info p {
            margin: 0;
            font-size: 0.82rem;
            line-height: 1.65;
        }

        .pch-tc__card-info ul {
            margin: 0.55rem 0 0;
            padding-inline-start: 1.15rem;
            font-size: 0.8rem;
            line-height: 1.7;
        }

        .pch-tc__card-info--amber {
            border-color: #fde68a;
            background: #fffbeb;
            color: #92400e;
        }

        .pch-tc__toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            margin-bottom: 0.85rem;
        }

        .pch-tc__count {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--pf-muted, #64748b);
        }

        .pch-tc__add {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.85rem;
            border-radius: 0.55rem;
            border: 1px dashed #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.8rem;
            font-weight: 800;
            cursor: pointer;
        }

        .pch-tc__add:hover {
            background: #dbeafe;
        }

        .pch-tc__empty {
            text-align: center;
            padding: 2.25rem 1.25rem;
            border: 1px dashed var(--pf-border, #e2e8f0);
            border-radius: 14px;
            background: var(--pf-soft, #f8fafc);
        }

        .pch-tc__empty-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 0.75rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            color: #64748b;
            font-size: 1.25rem;
        }

        .pch-tc__empty h6 {
            margin: 0 0 0.35rem;
            font-weight: 800;
            color: var(--pf-ink, #0f172a);
        }

        .pch-tc__empty p {
            margin: 0;
            font-size: 0.82rem;
            color: var(--pf-muted, #64748b);
            line-height: 1.6;
            max-width: 28rem;
            margin-inline: auto;
        }

        .pch-tc__row {
            border: 1px solid var(--pf-border, #e2e8f0);
            border-radius: 14px;
            background: #fff;
            padding: 1rem;
            margin-bottom: 0.85rem;
        }

        .pch-tc__row-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.85rem;
            padding-bottom: 0.65rem;
            border-bottom: 1px solid var(--pf-border, #e2e8f0);
        }

        .pch-tc__row-title {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--pf-ink, #0f172a);
        }

        .pch-tc__remove {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border: 0;
            background: transparent;
            color: #b91c1c;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            padding: 0.25rem 0.4rem;
        }

        .pch-tc__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.85rem;
        }

        @media (max-width: 767.98px) {
            .pch-tc__grid { grid-template-columns: 1fr; }
        }

        .pch-tc__meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 0.85rem;
        }

        @media (max-width: 575.98px) {
            .pch-tc__meta { grid-template-columns: 1fr; }
        }

        .pch-tc__meta .form-check {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 2.55rem;
            padding: 0.45rem 0.7rem;
            border: 1px solid var(--pf-border, #e2e8f0);
            border-radius: 0.6rem;
            background: var(--pf-soft, #f8fafc);
        }

        [data-theme-mode="dark"] .pch-tc__card-info {
            background: rgba(37, 99, 235, 0.16);
            border-color: rgba(147, 197, 253, 0.35);
            color: #bfdbfe;
        }

        [data-theme-mode="dark"] .pch-tc__card-info--amber {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(252, 211, 77, 0.35);
            color: #fde68a;
        }

        [data-theme-mode="dark"] .pch-tc__row,
        [data-theme-mode="dark"] .pch-tc__empty {
            background: rgba(15, 23, 42, 0.45);
        }
    </style>
@endpush

@section('content')
    @php
        $isWeb = $challenge->isWebSandbox();
        $cases = $challenge->testCases;
    @endphp

    <div class="main-content app-content pch-form">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="pch-form__hero">
                <div>
                    <nav>
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('programming-challenges.index') }}">التحديات البرمجية</a></li>
                            <li class="breadcrumb-item active">حالات الاختبار</li>
                        </ol>
                    </nav>
                    <h5 class="page-title fs-21">حالات الاختبار — {{ $challenge->title }}</h5>
                    <p>
                        @if($isWeb)
                            خطوة اختيارية لتحديات الويب — التقييم الحالي يدوي من لوحة التصحيح.
                        @else
                            حالات إدخال/إخراج للتحقق الآلي من حل الطالب.
                        @endif
                    </p>
                    <div class="pch-langs__steps">
                        <span class="pch-langs__step is-done">1 · اللغات</span>
                        <span class="pch-langs__step is-done">2 · الكود الابتدائي</span>
                        <span class="pch-langs__step is-active">3 · الاختبارات</span>
                    </div>
                </div>
                <a href="{{ route('programming-challenges.manage-starter', $challenge->id) }}" class="pch-form__side-link">
                    <i class="fe fe-terminal"></i> الكود الابتدائي
                </a>
            </div>

            <form action="{{ route('programming-challenges.update-test-cases', $challenge->id) }}" method="POST" id="test-cases-form">
                @csrf @method('PUT')

                <div class="pch-form__panel">
                    <div class="pch-form__panel-head">
                        <span class="pch-form__panel-icon pch-form__panel-icon--green"><i class="fe fe-check-square"></i></span>
                        <div>
                            <h6 class="pch-form__panel-title">ماذا تعني هذه الخطوة؟</h6>
                            <p class="pch-form__panel-sub">آخر خطوة في إعداد التحدي قبل ظهوره للطلاب</p>
                        </div>
                    </div>

                    <div class="pch-form__panel-body">
                        <div class="pch-tc__explain">
                            @if($isWeb)
                                <div class="pch-tc__card-info">
                                    <h6>تحدي ويب (HTML / CSS / JS)</h6>
                                    <p>
                                        الطالب يبني صفحة في المحرر والمعاينة الحية، والمعلّم يصحّح المحاولة يدوياً
                                        (معاينة الكود + درجة + ملاحظات). لذلك <strong>لا تحتاج حالات اختبار الآن</strong>
                                        لإكمال التحدي — يمكنك الضغط على «حفظ وإنهاء» مباشرة.
                                    </p>
                                    <ul>
                                        <li>حالات الاختبار هنا مخصّصة أساساً لتحديات <em>تنفيذ الكود</em> (إدخال → مخرج متوقع).</li>
                                        <li>يمكن ترك القائمة فارغة حالياً؛ التقييم الآلي لتحديات الويب مرحلة لاحقة إن لزم.</li>
                                    </ul>
                                </div>
                                <div class="pch-tc__card-info pch-tc__card-info--amber">
                                    <h6>هل أضيف حالات؟</h6>
                                    <p>
                                        اختياري فقط للتحضير المستقبلي. إن أضفت حالات ستُحفظ مع التحدي، لكن مسار التصحيح الحالي يبقى يدوياً.
                                    </p>
                                </div>
                            @else
                                <div class="pch-tc__card-info">
                                    <h6>تحدي تنفيذ كود (سيرفر)</h6>
                                    <p>
                                        كل حالة اختبار = مدخل يُمرَّر لبرنامج الطالب + المخرج المتوقع للمقارنة.
                                        عند التشغيل يُحسب النجاح/الفشل ونقاط كل حالة تلقائياً.
                                    </p>
                                    <ul>
                                        <li><strong>مخفي:</strong> لا يظهر للطالب أثناء الحل (لمنع الغش).</li>
                                        <li><strong>النقاط:</strong> وزن الحالة ضمن الدرجة الكلية.</li>
                                        <li><strong>المهلة:</strong> أقصى زمن تنفيذ قبل اعتبار الحالة فاشلة.</li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div class="pch-tc__toolbar">
                            <span class="pch-tc__count" id="tc-count-label">
                                {{ $cases->count() }} حالة اختبار
                            </span>
                            <button type="button" class="pch-tc__add" id="add-test-case">
                                <i class="fe fe-plus"></i> إضافة حالة
                            </button>
                        </div>

                        <div id="test-cases-container">
                            @forelse($cases as $i => $tc)
                                <div class="pch-tc__row test-case-row" data-index="{{ $i }}">
                                    <div class="pch-tc__row-head">
                                        <h6 class="pch-tc__row-title">حالة #{{ $i + 1 }}</h6>
                                        <button type="button" class="pch-tc__remove" data-remove-tc>
                                            <i class="fe fe-trash-2"></i> حذف
                                        </button>
                                    </div>
                                    <div class="pch-tc__grid">
                                        <div>
                                            <label class="pch-form__label">المدخل (stdin)</label>
                                            <textarea name="test_cases[{{ $i }}][input]" class="form-control font-monospace" rows="4" dir="ltr" style="text-align:left">{{ $tc->input }}</textarea>
                                        </div>
                                        <div>
                                            <label class="pch-form__label">المخرج المتوقع (stdout)</label>
                                            <textarea name="test_cases[{{ $i }}][expected_output]" class="form-control font-monospace" rows="4" dir="ltr" style="text-align:left">{{ $tc->expected_output }}</textarea>
                                        </div>
                                    </div>
                                    <div class="pch-tc__meta">
                                        <div>
                                            <label class="pch-form__label">النقاط</label>
                                            <input type="number" name="test_cases[{{ $i }}][points]" class="form-control" value="{{ $tc->points }}" step="0.01" min="0">
                                        </div>
                                        <div>
                                            <label class="pch-form__label">المهلة (ms)</label>
                                            <input type="number" name="test_cases[{{ $i }}][timeout_ms]" class="form-control" value="{{ $tc->timeout_ms }}" min="100">
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="test_cases[{{ $i }}][is_hidden]" value="1" class="form-check-input" id="hidden_{{ $i }}" @checked($tc->is_hidden)>
                                            <label class="form-check-label fw-semibold" for="hidden_{{ $i }}">مخفي عن الطالب</label>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="pch-tc__empty" id="empty-msg">
                                    <div class="pch-tc__empty-icon"><i class="fe fe-inbox"></i></div>
                                    <h6>لا توجد حالات اختبار</h6>
                                    <p>
                                        @if($isWeb)
                                            هذا طبيعي لتحدي ويب. احفظ وأنهِ الإعداد، أو أضف حالات اختيارية إن رغبت لاحقاً.
                                        @else
                                            أضف حالة واحدة على الأقل لتمكين التحقق الآلي من حلول الطلاب.
                                        @endif
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="pch-tc__footer">
                        <a href="{{ route('programming-challenges.manage-starter', $challenge->id) }}" class="pch-form__side-link">
                            <i class="fe fe-arrow-right"></i> رجوع للكود الابتدائي
                        </a>
                        <button type="submit" class="pch-form__submit" style="width: auto; min-width: 12rem;">
                            @if($isWeb && $cases->isEmpty())
                                حفظ وإنهاء الإعداد
                            @else
                                حفظ وإنهاء
                            @endif
                            <i class="fe fe-check"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@push('scripts')
<script>
(function () {
    var tcIndex = {{ $cases->count() }};
    var container = document.getElementById('test-cases-container');
    var countLabel = document.getElementById('tc-count-label');

    function refreshTitles() {
        var rows = container.querySelectorAll('.test-case-row');
        rows.forEach(function (row, i) {
            var title = row.querySelector('.pch-tc__row-title');
            if (title) title.textContent = 'حالة #' + (i + 1);
        });
        if (countLabel) {
            countLabel.textContent = rows.length + ' حالة اختبار';
        }
        if (rows.length === 0 && !document.getElementById('empty-msg')) {
            container.insertAdjacentHTML('beforeend', emptyHtml());
        }
    }

    function emptyHtml() {
        return '<div class="pch-tc__empty" id="empty-msg">' +
            '<div class="pch-tc__empty-icon"><i class="fe fe-inbox"></i></div>' +
            '<h6>لا توجد حالات اختبار</h6>' +
            '<p>{{ $isWeb ? "هذا طبيعي لتحدي ويب. احفظ وأنهِ الإعداد، أو أضف حالات اختيارية إن رغبت لاحقاً." : "أضف حالة واحدة على الأقل لتمكين التحقق الآلي من حلول الطلاب." }}</p>' +
            '</div>';
    }

    function rowHtml(i) {
        return '<div class="pch-tc__row test-case-row" data-index="' + i + '">' +
            '<div class="pch-tc__row-head">' +
            '<h6 class="pch-tc__row-title">حالة #' + (i + 1) + '</h6>' +
            '<button type="button" class="pch-tc__remove" data-remove-tc><i class="fe fe-trash-2"></i> حذف</button>' +
            '</div>' +
            '<div class="pch-tc__grid">' +
            '<div><label class="pch-form__label">المدخل (stdin)</label>' +
            '<textarea name="test_cases[' + i + '][input]" class="form-control font-monospace" rows="4" dir="ltr" style="text-align:left"></textarea></div>' +
            '<div><label class="pch-form__label">المخرج المتوقع (stdout)</label>' +
            '<textarea name="test_cases[' + i + '][expected_output]" class="form-control font-monospace" rows="4" dir="ltr" style="text-align:left"></textarea></div>' +
            '</div>' +
            '<div class="pch-tc__meta">' +
            '<div><label class="pch-form__label">النقاط</label>' +
            '<input type="number" name="test_cases[' + i + '][points]" class="form-control" value="1" step="0.01" min="0"></div>' +
            '<div><label class="pch-form__label">المهلة (ms)</label>' +
            '<input type="number" name="test_cases[' + i + '][timeout_ms]" class="form-control" value="5000" min="100"></div>' +
            '<div class="form-check">' +
            '<input type="checkbox" name="test_cases[' + i + '][is_hidden]" value="1" class="form-check-input" id="hidden_' + i + '" checked>' +
            '<label class="form-check-label fw-semibold" for="hidden_' + i + '">مخفي عن الطالب</label>' +
            '</div></div></div>';
    }

    document.getElementById('add-test-case')?.addEventListener('click', function () {
        document.getElementById('empty-msg')?.remove();
        container.insertAdjacentHTML('beforeend', rowHtml(tcIndex));
        tcIndex++;
        refreshTitles();
    });

    container?.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove-tc]');
        if (!btn) return;
        btn.closest('.test-case-row')?.remove();
        refreshTitles();
    });
})();
</script>
@endpush
