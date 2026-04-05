<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير تصحيح — {{ $attempt->quiz?->title ?? 'اختبار' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            direction: rtl;
            color: #222;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 8px 0;
            text-align: center;
        }
        .meta {
            margin-bottom: 16px;
            line-height: 1.6;
            border: 1px solid #ccc;
            padding: 10px;
            background: #f9f9f9;
        }
        .meta strong { display: inline-block; min-width: 120px; }
        .summary {
            margin-bottom: 14px;
            padding: 8px;
            border: 1px solid #333;
        }
        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.detail th, table.detail td {
            border: 1px solid #999;
            padding: 6px;
            vertical-align: top;
            text-align: right;
        }
        table.detail th {
            background: #eee;
            font-weight: bold;
        }
        .q-num { width: 28px; white-space: nowrap; }
        .small-muted { font-size: 9px; color: #555; }
        .badge-ok { color: #0a0; font-weight: bold; }
        .badge-bad { color: #a00; font-weight: bold; }
    </style>
</head>
<body>
    <h1>تقرير تصحيح الاختبار</h1>

    <div class="meta">
        <div><strong>الاختبار:</strong> {{ $attempt->quiz?->title ?? '—' }}</div>
        <div><strong>الطالب:</strong> {{ $attempt->student?->name ?? '—' }}</div>
        <div><strong>البريد:</strong> {{ $attempt->student?->email ?? '—' }}</div>
        <div><strong>المحاولة:</strong> #{{ $attempt->attempt_number }}</div>
        <div><strong>تاريخ التسليم:</strong> {{ $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d H:i') : '—' }}</div>
        @if($attempt->graded_at)
            <div><strong>تاريخ التصحيح:</strong> {{ $attempt->graded_at->format('Y-m-d H:i') }}</div>
        @endif
    </div>

    <div class="summary">
        <strong>الملخص:</strong>
        الدرجة {{ number_format((float) ($attempt->total_score ?? 0), 2) }} / {{ number_format((float) ($attempt->max_score ?? 0), 2) }}
        — النسبة {{ number_format((float) ($attempt->percentage_score ?? 0), 1) }}٪
        — {{ $attempt->passed ? 'ناجح' : 'راسب' }}
    </div>

    <table class="detail">
        <thead>
            <tr>
                <th class="q-num">#</th>
                <th>السؤال</th>
                <th>النوع</th>
                <th>إجابة الطالب</th>
                <th>الإجابة الصحيحة</th>
                <th>الدرجة</th>
                <th>النتيجة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($responses as $idx => $response)
                @php
                    $q = $response->question;
                    $studentP = $presenter->studentAnswerPlain($response);
                    $correctP = $presenter->correctAnswerPlain($q);
                    $typeLabel = $response->questionType?->display_name ?? $q?->questionType?->display_name ?? '—';
                    $qText = $q ? strip_tags($q->question_text) : 'سؤال محذوف من البنك';
                    $hasImage = $q && !empty($q->question_image);
                @endphp
                <tr>
                    <td class="q-num">{{ $idx + 1 }}</td>
                    <td>
                        {{ $qText }}
                        @if($hasImage)
                            <div class="small-muted">(يحتوي السؤال على صورة)</div>
                        @endif
                    </td>
                    <td>{{ $typeLabel }}</td>
                    <td>{{ $studentP }}</td>
                    <td>{{ $correctP }}</td>
                    <td>{{ $response->score_obtained }} / {{ $response->max_score }}</td>
                    <td>
                        @if($response->is_correct)
                            <span class="badge-ok">صحيح</span>
                        @else
                            <span class="badge-bad">خطأ</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
