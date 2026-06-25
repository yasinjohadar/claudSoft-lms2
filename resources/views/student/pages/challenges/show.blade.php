@extends('student.layouts.master')

@section('page-title')
    {{ $challenge->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="my-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('student.challenges.index') }}">التحديات</a></li>
                        <li class="breadcrumb-item active">{{ $challenge->title }}</li>
                    </ol>
                </nav>
            </div>

            @include('shared.quizzes.intro-panel', [
                'title' => $challenge->title,
                'description' => $challenge->description,
                'instructions' => $challenge->instructions,
                'heroVariant' => 'question_module',
                'heroIcon' => 'fe-code',
                'stats' => [
                    ['icon' => 'fe-star', 'label' => 'الدرجة القصوى', 'value' => $challenge->max_score, 'color' => 'gold'],
                    ['icon' => 'fe-refresh-cw', 'label' => 'المحاولات', 'value' => $challenge->attempts_allowed, 'color' => 'cyan'],
                ],
                'showHistory' => true,
                'completedAttempts' => $attempts->whereIn('status', ['submitted', 'graded'])->count(),
                'attemptsAllowed' => $challenge->attempts_allowed,
                'lastScore' => ($lastAttempt && $lastAttempt->isGraded()) ? $lastAttempt->score . '/' . $lastAttempt->max_score : null,
                'lastPassed' => false,
                'reviewUrl' => null,
                'inProgressAttempt' => $inProgress,
                'continueUrl' => $inProgress ? route('student.challenges.work', $challenge->id) : null,
                'canAttempt' => $canAttempt || $inProgress,
                'startUrl' => route('student.challenges.start', $challenge->id),
                'remainingAttempts' => $challenge->attempts_allowed - $attempts->whereIn('status', ['submitted', 'graded'])->count(),
                'blockedLabel' => 'استنفدت المحاولات',
                'blockedHint' => 'لقد استخدمت جميع المحاولات المسموحة',
                'tips' => [
                    ['icon' => 'fe-monitor', 'text' => 'استخدم متصفحاً حديثاً'],
                    ['icon' => 'fe-save', 'text' => 'يُحفظ تلقائياً كل 30 ثانية'],
                ],
            ])
        </div>
    </div>
@stop
