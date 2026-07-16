<?php

namespace App\Services\ProgrammingChallenge;

use App\Models\CourseModule;
use App\Models\ProgrammingChallenge;
use App\Models\ProgrammingChallengeAttempt;
use App\Models\ProgrammingChallengeSubmission;
use App\Models\ProgrammingChallengeSubmissionFile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChallengeSubmissionService
{
    public function __construct(
        protected ChallengeAutoGradingService $autoGradingService
    ) {}
    /**
     * Start or resume an in-progress attempt.
     */
    public function startOrResumeAttempt(
        ProgrammingChallenge $challenge,
        User $student,
        ?int $courseModuleId = null
    ): ProgrammingChallengeAttempt {
        $inProgress = ProgrammingChallengeAttempt::where('programming_challenge_id', $challenge->id)
            ->where('user_id', $student->id)
            ->where('status', 'in_progress')
            ->when($courseModuleId, fn ($q) => $q->where('course_module_id', $courseModuleId))
            ->first();

        if ($inProgress) {
            $this->restoreDraftFromPreviousIfNeeded($inProgress, $challenge);

            return $inProgress;
        }

        $attemptNumber = ProgrammingChallengeAttempt::where('programming_challenge_id', $challenge->id)
            ->where('user_id', $student->id)
            ->max('attempt_number') + 1;

        $attempt = ProgrammingChallengeAttempt::create([
            'programming_challenge_id' => $challenge->id,
            'user_id' => $student->id,
            'course_module_id' => $courseModuleId,
            'attempt_number' => $attemptNumber,
            'status' => 'in_progress',
            'started_at' => now(),
            'max_score' => $challenge->max_score,
        ]);

        $this->ensureDraftSubmission($attempt, $challenge);

        return $attempt;
    }

    /**
     * Save draft files for the current attempt.
     */
    public function saveDraft(
        ProgrammingChallengeAttempt $attempt,
        array $files,
        ?string $studentNotes = null
    ): ProgrammingChallengeSubmission {
        if (! $attempt->isInProgress()) {
            throw new \RuntimeException('لا يمكن حفظ مسودة لمحاولة مُسلَّمة');
        }

        $submission = $this->ensureDraftSubmission($attempt, $attempt->challenge);

        if ($studentNotes !== null) {
            $submission->student_notes = $studentNotes;
            $submission->save();
        }

        $this->syncSubmissionFiles($submission, $files);

        return $submission->fresh('files');
    }

    /**
     * Submit the attempt for grading.
     */
    /**
     * @return array{submission: ProgrammingChallengeSubmission, auto_grade: ?array}
     */
    public function submit(
        ProgrammingChallengeAttempt $attempt,
        array $files,
        ?string $studentNotes = null
    ): array {
        if (! $attempt->isInProgress()) {
            throw new \RuntimeException('هذه المحاولة مُسلَّمة بالفعل');
        }

        return DB::transaction(function () use ($attempt, $files, $studentNotes) {
            $challenge = $attempt->challenge;

            $draft = $attempt->draftSubmission;
            if ($draft) {
                $draft->delete();
            }

            $submissionNumber = ProgrammingChallengeSubmission::where('programming_challenge_attempt_id', $attempt->id)
                ->max('submission_number') + 1;

            $submission = ProgrammingChallengeSubmission::create([
                'programming_challenge_attempt_id' => $attempt->id,
                'submission_number' => max(1, $submissionNumber),
                'status' => 'submitted',
                'student_notes' => $studentNotes,
            ]);

            $this->syncSubmissionFiles($submission, $files);

            $gradeStatus = in_array($challenge->grading_mode, ['auto', 'hybrid']) ? 'pending' : 'pending';

            $attempt->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'grade_status' => $gradeStatus,
            ]);

            if ($attempt->course_module_id) {
                $this->markModuleSubmitted($attempt);
            }

            $submission = $submission->fresh('files');
            $autoGrade = null;

            if ($challenge->isCodeRunner() && in_array($challenge->grading_mode, ['auto', 'hybrid'], true)) {
                $autoGrade = $this->autoGradingService->gradeSubmission(
                    $attempt->fresh(),
                    $submission,
                    $files
                );
            }

            return [
                'submission' => $submission,
                'auto_grade' => $autoGrade,
            ];
        });
    }

    /**
     * Grade an attempt manually.
     */
    public function gradeAttempt(
        ProgrammingChallengeAttempt $attempt,
        float $score,
        ?string $feedback,
        int $gradedBy
    ): ProgrammingChallengeAttempt {
        $attempt->update([
            'status' => 'graded',
            'score' => min($score, $attempt->max_score ?? $attempt->challenge->max_score),
            'feedback' => $feedback,
            'graded_by' => $gradedBy,
            'graded_at' => now(),
            'grade_status' => 'graded',
        ]);

        if ($attempt->course_module_id) {
            $this->markModuleCompleted($attempt);
        }

        return $attempt->fresh();
    }

    protected function ensureDraftSubmission(
        ProgrammingChallengeAttempt $attempt,
        ProgrammingChallenge $challenge
    ): ProgrammingChallengeSubmission {
        $draft = $attempt->draftSubmission;

        if ($draft) {
            return $draft;
        }

        $submission = ProgrammingChallengeSubmission::create([
            'programming_challenge_attempt_id' => $attempt->id,
            'submission_number' => 0,
            'status' => 'draft',
        ]);

        foreach ($this->resolveDraftSeedFiles($attempt, $challenge) as $seed) {
            ProgrammingChallengeSubmissionFile::create([
                'programming_challenge_submission_id' => $submission->id,
                'programming_language_id' => $seed->programming_language_id,
                'file_role' => $seed->file_role,
                'filename' => $seed->filename,
                'content' => $seed->content ?? '',
            ]);
        }

        return $submission;
    }

    /**
     * Seed a new attempt from the student's last submitted work when available,
     * otherwise fall back to the challenge starter files.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    protected function resolveDraftSeedFiles(
        ProgrammingChallengeAttempt $attempt,
        ProgrammingChallenge $challenge
    ) {
        $previousFiles = $this->previousSubmittedFiles($attempt, $challenge);

        if ($previousFiles && $previousFiles->isNotEmpty()) {
            return $previousFiles;
        }

        return $challenge->files;
    }

    /**
     * If an in-progress draft still matches the starter template, restore the
     * student's previous submission so a graded attempt is not "lost".
     */
    public function restoreDraftFromPreviousIfNeeded(
        ProgrammingChallengeAttempt $attempt,
        ProgrammingChallenge $challenge
    ): void {
        if (! $attempt->isInProgress()) {
            return;
        }

        $previousFiles = $this->previousSubmittedFiles($attempt, $challenge);

        if (! $previousFiles || $previousFiles->isEmpty()) {
            return;
        }

        $draft = $this->ensureDraftSubmission($attempt, $challenge)->load('files');

        $shouldRestore = $draft->files->isEmpty()
            || $this->filesMatchStarter($draft->files, $challenge->files)
            || $this->filesMatchDefaultWebStarter($draft->files, $challenge);

        if (! $shouldRestore) {
            return;
        }

        $this->syncSubmissionFiles(
            $draft,
            $previousFiles->map(function ($file) {
                return [
                    'programming_language_id' => $file->programming_language_id,
                    'file_role' => $file->file_role,
                    'filename' => $file->filename,
                    'content' => $file->content ?? '',
                ];
            })->all()
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>|null
     */
    protected function previousSubmittedFiles(
        ProgrammingChallengeAttempt $attempt,
        ProgrammingChallenge $challenge
    ) {
        $previousAttempt = ProgrammingChallengeAttempt::query()
            ->where('programming_challenge_id', $challenge->id)
            ->where('user_id', $attempt->user_id)
            ->where('id', '!=', $attempt->id)
            ->whereIn('status', ['submitted', 'graded', 'returned'])
            ->when(
                $attempt->course_module_id,
                fn ($q) => $q->where('course_module_id', $attempt->course_module_id),
                fn ($q) => $q->whereNull('course_module_id')
            )
            ->orderByDesc('attempt_number')
            ->first();

        if (! $previousAttempt) {
            return null;
        }

        $previousSubmission = ProgrammingChallengeSubmission::query()
            ->where('programming_challenge_attempt_id', $previousAttempt->id)
            ->where('status', '!=', 'draft')
            ->orderByDesc('submission_number')
            ->orderByDesc('id')
            ->with('files')
            ->first();

        if (! $previousSubmission || $previousSubmission->files->isEmpty()) {
            return null;
        }

        return $previousSubmission->files;
    }

    protected function filesMatchStarter($draftFiles, $starterFiles): bool
    {
        if ($starterFiles->isEmpty()) {
            return false;
        }

        $draftMap = collect($draftFiles)->keyBy(function ($file) {
            return strtolower((string) ($file->file_role ?: $file->filename));
        });

        foreach ($starterFiles as $starter) {
            $key = strtolower((string) ($starter->file_role ?: $starter->filename));
            $draft = $draftMap->get($key);

            if (! $draft) {
                return false;
            }

            if (trim((string) ($draft->content ?? '')) !== trim((string) ($starter->content ?? ''))) {
                return false;
            }
        }

        return true;
    }

    protected function filesMatchDefaultWebStarter($draftFiles, ProgrammingChallenge $challenge): bool
    {
        if (! $challenge->isWebSandbox() || $challenge->files->isNotEmpty()) {
            return false;
        }

        $defaults = [
            'html' => "<h1>مرحباً</h1>\n<p>ابدأ التعديل هنا</p>",
            'css' => 'body { font-family: sans-serif; padding: 1rem; }',
            'js' => '',
        ];

        $draftMap = collect($draftFiles)->keyBy(function ($file) {
            return strtolower((string) ($file->file_role ?: ''));
        });

        foreach ($defaults as $role => $content) {
            $draft = $draftMap->get($role);
            if (! $draft) {
                continue;
            }
            if (trim((string) ($draft->content ?? '')) !== trim($content)) {
                return false;
            }
        }

        return $draftMap->isNotEmpty();
    }

    protected function syncSubmissionFiles(ProgrammingChallengeSubmission $submission, array $files): void
    {
        $submission->files()->delete();

        foreach ($files as $file) {
            if (empty($file['content']) && empty($file['file_role'])) {
                continue;
            }

            ProgrammingChallengeSubmissionFile::create([
                'programming_challenge_submission_id' => $submission->id,
                'programming_language_id' => $file['programming_language_id'] ?? null,
                'file_role' => $file['file_role'] ?? 'starter',
                'filename' => $file['filename'] ?? 'code.txt',
                'content' => $file['content'] ?? '',
            ]);
        }
    }

    protected function markModuleSubmitted(ProgrammingChallengeAttempt $attempt): void
    {
        $module = CourseModule::find($attempt->course_module_id);
        if (! $module) {
            return;
        }

        $module->completions()->updateOrCreate(
            ['student_id' => $attempt->user_id],
            ['completion_status' => 'in_progress']
        );
    }

    protected function markModuleCompleted(ProgrammingChallengeAttempt $attempt): void
    {
        $module = CourseModule::find($attempt->course_module_id);
        if (! $module) {
            return;
        }

        $module->markAsCompletedBy($attempt->student, (float) $attempt->score);
    }
}
