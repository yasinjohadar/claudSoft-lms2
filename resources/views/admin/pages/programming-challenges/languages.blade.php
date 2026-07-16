@extends('admin.layouts.master')

@section('page-title')
    لغات التحدي — {{ $challenge->title }}
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

        .pch-langs__footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border-top: 1px solid var(--pf-border, #e2e8f0);
            background: var(--pf-soft, #f8fafc);
        }

        /* Web stack — one package */
        .pch-stack {
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            background: #f8fbff;
            overflow: hidden;
        }

        .pch-stack__head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid #dbeafe;
        }

        .pch-stack__title {
            margin: 0 0 0.3rem;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--pf-ink, #0f172a);
        }

        .pch-stack__sub {
            margin: 0;
            font-size: 0.85rem;
            color: var(--pf-muted, #64748b);
            line-height: 1.55;
            max-width: 36rem;
        }

        .pch-stack__pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
            background: #dbeafe;
            color: #1d4ed8;
            white-space: nowrap;
        }

        .pch-stack__chain {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            gap: 0;
            padding: 1.15rem 1.25rem 1.35rem;
        }

        .pch-stack__item {
            flex: 1 1 10rem;
            min-width: 9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .pch-stack__item:first-child {
            border-radius: 12px 0 0 12px;
        }

        .pch-stack__item:last-child {
            border-radius: 0 12px 12px 0;
        }

        @media (max-width: 767.98px) {
            .pch-stack__item {
                border-radius: 12px !important;
                flex: 1 1 100%;
            }

            .pch-stack__join {
                display: none !important;
            }
        }

        .pch-stack__join {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            flex-shrink: 0;
            color: #94a3b8;
            font-weight: 800;
            font-size: 0.9rem;
        }

        .pch-stack__badge {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 0.78rem;
            flex-shrink: 0;
        }

        .pch-stack__name {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--pf-ink, #0f172a);
        }

        .pch-stack__file {
            margin: 0.1rem 0 0;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        /* Code runner — multi select */
        .pch-langs__grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
        }

        @media (max-width: 991.98px) {
            .pch-langs__grid { grid-template-columns: 1fr; }
        }

        .pch-langs__card {
            position: relative;
            border: 1px solid var(--pf-border, #e2e8f0);
            border-radius: 14px;
            background: #fff;
            padding: 1.1rem 1rem 1rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            height: 100%;
        }

        .pch-langs__card.is-selected {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .pch-langs__badge {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
        }

        .pch-langs__name {
            margin: 0 0 0.25rem;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--pf-ink, #0f172a);
        }

        .pch-langs__desc {
            margin: 0 0 0.85rem;
            font-size: 0.8rem;
            color: var(--pf-muted, #64748b);
            line-height: 1.55;
            min-height: 2.5rem;
        }

        .pch-langs__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-bottom: 0.85rem;
        }

        .pch-langs__tag {
            display: inline-flex;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #334155;
        }

        .pch-langs__default {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
        }

        [data-theme-mode="dark"] .pch-stack,
        [data-theme-mode="dark"] .pch-stack__item,
        [data-theme-mode="dark"] .pch-langs__card {
            background: rgba(15, 23, 42, 0.45);
        }
    </style>
@endpush

@section('content')
    @php
        $isWeb = $challenge->isWebSandbox();
        $defaultId = (int) ($challenge->languages->firstWhere('pivot.is_default', true)?->id
            ?? ($selectedIds[0] ?? 0));
        $webMeta = [
            'html' => ['label' => 'HTML', 'file' => 'index.html', 'color' => '#E34F26'],
            'css' => ['label' => 'CSS', 'file' => 'style.css', 'color' => '#1572B6'],
            'javascript' => ['label' => 'JS', 'file' => 'script.js', 'color' => '#B8A000'],
        ];
        $htmlLang = $languages->firstWhere('slug', 'html');
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
                            <li class="breadcrumb-item active">اللغات</li>
                        </ol>
                    </nav>
                    <h5 class="page-title fs-21">اختيار اللغات — {{ $challenge->title }}</h5>
                    <p>
                        @if($isWeb)
                            تحدي ويب: تُفعَّل حزمة الواجهة الأمامية كاملة (HTML + CSS + JS) معاً للطالب.
                        @else
                            حدد لغات التنفيذ المتاحة للطلاب في هذا التحدي.
                        @endif
                    </p>
                    <div class="pch-langs__steps">
                        <span class="pch-langs__step is-active">1 · اللغات</span>
                        <span class="pch-langs__step">2 · الكود الابتدائي</span>
                        <span class="pch-langs__step">3 · الاختبارات</span>
                    </div>
                </div>
                <a href="{{ route('programming-challenges.edit', $challenge->id) }}" class="pch-form__side-link">
                    <i class="fe fe-edit-2"></i> تعديل التحدي
                </a>
            </div>

            <form action="{{ route('programming-challenges.update-languages', $challenge->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="pch-form__panel">
                    <div class="pch-form__panel-head">
                        <span class="pch-form__panel-icon"><i class="fe fe-code"></i></span>
                        <div>
                            <h6 class="pch-form__panel-title">
                                @if($isWeb) حزمة لغات التحدي @else اللغات القابلة للتشغيل @endif
                            </h6>
                            <p class="pch-form__panel-sub">
                                @if($isWeb)
                                    تُضاف اللغات الثلاث كوحدة واحدة — لا اختيار منفصل لكل لغة
                                @else
                                    اختر لغة واحدة على الأقل ثم حدد الافتراضية
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="pch-form__panel-body">
                        @if($languages->isEmpty())
                            <div class="alert alert-warning mb-0">
                                لا توجد لغات قابلة للتشغيل حالياً. لتحديات التنفيذ شغّل
                                <code>php artisan db:seed --class=ProgrammingLanguageExecutionSeeder</code>
                            </div>
                        @elseif($isWeb)
                            <div class="pch-stack">
                                <div class="pch-stack__head">
                                    <div>
                                        <h6 class="pch-stack__title">واجهة ويب أمامية</h6>
                                        <p class="pch-stack__sub">
                                            حزمة واحدة للطالب في المحرر والمعاينة الحية: هيكل الصفحة، التنسيق، والتفاعل — معاً وليس كملفات منفصلة الاختيار.
                                        </p>
                                    </div>
                                    <span class="pch-stack__pill">
                                        <i class="fe fe-check-circle"></i>
                                        مفعّلة تلقائياً
                                    </span>
                                </div>

                                <div class="pch-stack__chain">
                                    @foreach($languages as $lang)
                                        @php
                                            $meta = $webMeta[$lang->slug] ?? [
                                                'label' => strtoupper(substr($lang->name, 0, 2)),
                                                'file' => $lang->default_filename,
                                                'color' => $lang->color ?: '#2563eb',
                                            ];
                                        @endphp
                                        <input type="hidden" name="languages[]" value="{{ $lang->id }}">
                                        <div class="pch-stack__item">
                                            <span class="pch-stack__badge" style="background: {{ $meta['color'] }}">{{ $meta['label'] }}</span>
                                            <div>
                                                <p class="pch-stack__name">{{ $lang->display_name }}</p>
                                                <p class="pch-stack__file">{{ $meta['file'] }}</p>
                                            </div>
                                        </div>
                                        @if(! $loop->last)
                                            <span class="pch-stack__join" aria-hidden="true">+</span>
                                        @endif
                                    @endforeach
                                </div>

                                @if($htmlLang)
                                    <input type="hidden" name="default_language" value="{{ $htmlLang->id }}">
                                @endif
                            </div>
                        @else
                            <div class="pch-langs__grid">
                                @foreach($languages as $lang)
                                    @php
                                        $checked = in_array($lang->id, $selectedIds, true);
                                        $color = $lang->color ?: '#2563eb';
                                        $short = strtoupper(substr($lang->name, 0, 2));
                                    @endphp
                                    <div class="pch-langs__card {{ $checked ? 'is-selected' : '' }}">
                                        <div class="pch-langs__badge" style="background: {{ $color }}">{{ $short }}</div>
                                        <h6 class="pch-langs__name">{{ $lang->display_name }}</h6>
                                        <p class="pch-langs__desc">{{ $lang->description ?: 'لغة متاحة في محرر التحدي' }}</p>
                                        <div class="pch-langs__meta">
                                            <span class="pch-langs__tag">سيرفر</span>
                                            @if($lang->file_extension)
                                                <span class="pch-langs__tag">.{{ $lang->file_extension }}</span>
                                            @endif
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input lang-toggle" type="checkbox"
                                                   name="languages[]" value="{{ $lang->id }}"
                                                   id="lang_{{ $lang->id }}"
                                                   @checked($checked)>
                                            <label class="form-check-label fw-semibold" for="lang_{{ $lang->id }}">تفعيل هذه اللغة</label>
                                        </div>
                                        <label class="pch-langs__default">
                                            <input type="radio" name="default_language" value="{{ $lang->id }}"
                                                   @checked((int) $defaultId === (int) $lang->id || ($defaultId === 0 && $loop->first))>
                                            افتراضي عند فتح المحرر
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pch-langs__footer">
                        <a href="{{ route('programming-challenges.index') }}" class="pch-form__side-link">
                            <i class="fe fe-arrow-right"></i> رجوع للقائمة
                        </a>
                        <button type="submit" class="pch-form__submit" style="width: auto; min-width: 14rem;">
                            حفظ والمتابعة للكود الابتدائي
                            <i class="fe fe-arrow-left"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@push('scripts')
@if(! $challenge->isWebSandbox())
<script>
document.querySelectorAll('.lang-toggle').forEach(function (input) {
    input.addEventListener('change', function () {
        var card = this.closest('.pch-langs__card');
        if (card) {
            card.classList.toggle('is-selected', this.checked);
        }
    });
});
</script>
@endif
@endpush
