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

        $starterFiles = $challenge->files;
        foreach ($starterFiles as $starter) {
            ProgrammingChallengeSubmissionFile::create([
                'programming_challenge_submission_id' => $submission->id,
                'programming_language_id' => $starter->programming_language_id,
                'file_role' => $starter->file_role,
                'filename' => $starter->filename,
                'content' => $starter->content,
            ]);
        }

        return $submission;
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
