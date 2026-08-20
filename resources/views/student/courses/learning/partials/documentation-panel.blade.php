@php
    /** @var \App\Models\DocumentationPage $docPage */
    /** @var \App\Models\CourseModule $module */
    $docTitle = $docPage->title ?? $module->title;
    $docContent = (string) ($docPage->content ?? '');

    // Approximate reading time from the doc body (Arabic reads ~180 wpm).
    $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($docContent)) ?? '');
    $wordCount = $plainText === '' ? 0 : count(preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY));
    $readingMinutes = $wordCount > 0 ? max(1, (int) ceil($wordCount / 180)) : null;

    // Outline of the document from its own headings — gives the student a clear
    // picture of what is inside before they open it.
    $outline = [];
    if ($docContent !== '' && preg_match_all('/<h([2-4])\b[^>]*>(.*?)<\/h\1>/isu', $docContent, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $heading = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($heading !== '') {
                $outline[] = ['level' => (int) $match[1], 'text' => $heading];
            }
        }
    }
    $outlineTotal = count($outline);
    $outlineShown = array_slice($outline, 0, 9);

    // Arabic counted-noun agreement: 1 مفرد / 2 مثنى / 3-10 جمع / 11+ تمييز مفرد
    $arabicCount = function (int $n, string $one, string $two, string $few, string $many): string {
        return match (true) {
            $n === 1 => $one,
            $n === 2 => $two,
            $n >= 3 && $n <= 10 => $n . ' ' . $few,
            default => $n . ' ' . $many,
        };
    };
@endphp

<article class="student-learn-doc-panel dashboard-fade-in">
    <div class="student-learn-doc-panel__hero">
        <div class="student-learn-doc-panel__hero-content">
            <span class="student-learn-doc-panel__icon" aria-hidden="true">
                <i class="ri-book-2-line"></i>
            </span>
            <div class="student-learn-doc-panel__hero-text min-w-0">
                <div class="student-learn-doc-panel__badges">
                    <span class="badge student-learn-doc-panel__type-badge">
                        <i class="ri-file-text-line me-1"></i>توثيق
                    </span>
                    @if($docPage->category)
                        <span class="badge bg-info-transparent text-info">
                            <i class="ri-folder-3-line me-1"></i>{{ $docPage->category->name }}
                        </span>
                    @endif
                </div>
                <h4 class="student-learn-doc-panel__title mb-0">{{ $docTitle }}</h4>
                <p class="student-learn-doc-panel__subtitle mb-0">
                    <i class="ri-double-quotes-r"></i>
                    درس على شكل صفحة توثيق — اقرأه ثم عد لتحديد الدرس كمكتمل.
                </p>
            </div>
        </div>
    </div>

    <div class="student-learn-doc-panel__body">
        @if($readingMinutes || $docPage->updated_at)
            <div class="student-learn-doc-panel__meta">
                @if($readingMinutes)
                    <span class="student-learn-doc-panel__meta-item">
                        <i class="ri-time-line"></i>
                        قراءة تقريبية:
                        <strong>{{ $arabicCount($readingMinutes, 'دقيقة واحدة', 'دقيقتان', 'دقائق', 'دقيقة') }}</strong>
                    </span>
                    <span class="student-learn-doc-panel__meta-item">
                        <i class="ri-text"></i>
                        <strong>{{ number_format($wordCount) }}</strong> كلمة
                    </span>
                @endif
                @if($outlineTotal)
                    <span class="student-learn-doc-panel__meta-item">
                        <i class="ri-list-unordered"></i>
                        <strong>{{ $arabicCount($outlineTotal, 'قسم واحد', 'قسمان', 'أقسام', 'قسماً') }}</strong>
                    </span>
                @endif
                @if($docPage->updated_at)
                    <span class="student-learn-doc-panel__meta-item">
                        <i class="ri-refresh-line"></i>
                        آخر تحديث: <strong>{{ $docPage->updated_at->locale('ar')->translatedFormat('j F Y') }}</strong>
                    </span>
                @endif
            </div>
        @endif

        @if($docPage->excerpt)
            <p class="student-learn-doc-panel__desc">{{ $docPage->excerpt }}</p>
        @endif

        @if($outlineShown)
            <section class="student-learn-doc-panel__outline" aria-label="محتويات التوثيق">
                <h6 class="student-learn-doc-panel__outline-title">
                    <i class="ri-list-check-2"></i>محتويات هذا التوثيق
                </h6>
                <ol class="student-learn-doc-panel__outline-list">
                    @foreach($outlineShown as $index => $item)
                        <li class="student-learn-doc-panel__outline-item student-learn-doc-panel__outline-item--l{{ $item['level'] }}">
                            <span class="student-learn-doc-panel__outline-index">{{ $index + 1 }}</span>
                            <span class="student-learn-doc-panel__outline-text">{{ $item['text'] }}</span>
                        </li>
                    @endforeach
                </ol>
                @if($outlineTotal > count($outlineShown))
                    @php $remainingSections = $outlineTotal - count($outlineShown); @endphp
                    <p class="student-learn-doc-panel__outline-more mb-0">
                        <i class="ri-more-line"></i>
                        و{{ $arabicCount($remainingSections, 'قسم واحد', 'قسمان', 'أقسام', 'قسماً') }} أخرى داخل الصفحة
                    </p>
                @endif
            </section>
        @endif

        <div class="student-learn-doc-panel__actions">
            <a href="{{ $docPage->publicUrl() }}" target="_blank" rel="noopener"
               class="btn btn-primary btn-lg rounded-pill student-learn-doc-panel__cta">
                <i class="ri-book-open-line me-2"></i>
                فتح صفحة التوثيق
            </a>

            @include('student.courses.learning.partials.documentation-pdf-export', [
                'docPage' => $docPage,
                'btnClass' => 'btn btn-outline-danger rounded-pill student-learn-doc-panel__secondary',
            ])

            <span class="student-learn-doc-panel__cta-hint">
                <i class="ri-window-line me-1"></i>يفتح في تبويب جديد
            </span>
        </div>
    </div>
</article>
