@extends('student.layouts.master')

@section('page-title')
    {{ $challenge->title }} — محرر الكود
@stop

@push('styles')
    @include('shared.challenges.assets', ['includeCss' => true, 'includeJs' => false])
@endpush

@section('content')
    <div class="main-content app-content challenge-ide-page">
        <div class="container-fluid challenge-ide-page__inner">
    @php
        $backUrl = $courseModule
            ? route('student.learn.module', $courseModule->id)
            : route('student.challenges.show', $challenge->id);

        $sourceFiles = ($draft?->files ?? $challenge->files);
        if ($sourceFiles->isEmpty() && $challenge->isWebSandbox()) {
            $sourceFiles = collect([
                (object) ['file_role' => 'html', 'filename' => 'index.html', 'content' => "<h1>مرحباً</h1>\n<p>ابدأ التعديل هنا</p>", 'programming_language_id' => null, 'language' => null],
                (object) ['file_role' => 'css', 'filename' => 'style.css', 'content' => "body { font-family: sans-serif; padding: 1rem; }", 'programming_language_id' => null, 'language' => null],
                (object) ['file_role' => 'js', 'filename' => 'script.js', 'content' => "", 'programming_language_id' => null, 'language' => null],
            ]);
        }
        $filesPayload = $sourceFiles->map(function ($f) {
            return [
                'file_role' => $f->file_role,
                'filename' => $f->filename,
                'content' => $f->content ?? '',
                'programming_language_id' => $f->programming_language_id,
                'monaco_language_id' => $f->language?->monaco_language_id ?? match($f->file_role) {
                    'html' => 'html', 'css' => 'css', 'js' => 'javascript', default => 'plaintext'
                },
                'tab_label' => $f->language?->display_name ?? strtoupper($f->file_role),
            ];
        })->values();

        $ideConfig = [
            'challengeId' => $challenge->id,
            'attemptId' => $attempt->id,
            'moduleId' => $courseModule?->id,
            'challengeType' => $challenge->challenge_type,
            'files' => $filesPayload,
            'api' => [
                'saveDraft' => route('student.challenges.save-draft', $challenge->id),
                'submit' => route('student.challenges.submit', $challenge->id),
                'run' => route('student.challenges.run', $challenge->id),
                'csrf' => route('student.challenges.csrf-token'),
            ],
            'csrf' => csrf_token(),
            'autoSaveInterval' => $challenge->getDefaultSettings()['auto_save_interval'] ?? 30,
            'backUrl' => $backUrl,
            'previewStoreUrl' => route('student.challenges.live-preview.store'),
        ];
    @endphp

    @include('shared.challenges.ide-layout', [
        'challenge' => $challenge,
        'attempt' => $attempt,
        'backUrl' => $backUrl,
        'ideConfig' => $ideConfig,
        'pistonAvailable' => app(\App\Services\CodeExecution\CodeExecutionService::class)->isAvailable(),
    ])
        </div>
    </div>
@stop

@push('scripts')
    @include('shared.challenges.assets', ['includeCss' => false, 'includeJs' => true])
    <script>
        window.__challengeIdeConfig = @json($ideConfig);
        (function () {
            function bootChallengeIde() {
                if (window.ChallengeIDE && window.__challengeIdeConfig) {
                    window.ChallengeIDE.init(window.__challengeIdeConfig);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootChallengeIde);
            } else {
                bootChallengeIde();
            }
        })();
    </script>
@endpush
