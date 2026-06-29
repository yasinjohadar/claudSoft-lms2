# Random Pool Quiz — Design Spec

## Goal
Quiz type `random_pool` that draws **N questions per attempt** from a unified bank of direct questions and linked question pools, excluding previously seen questions when possible and recycling with a student warning when the bank is exhausted.

## Design Decisions
| Decision | Choice |
|----------|--------|
| Bank source | Direct `quiz_questions.question_id` + `quiz_questions.question_pool_id` |
| Merge strategy | Single unified candidate pool (dedupe by `question_id`) |
| Between attempts | Exclude questions from prior submitted/graded/reviewing attempts |
| When exhausted | Fill remainder from previously used questions; `selection_meta.recycled = true` |
| Scoring | `attempt.max_score` = sum of selected question grades only |

## Schema
**`quizzes`**
- `quiz_type` includes `random_pool`
- `questions_per_attempt` (nullable; required when type is `random_pool`)

**`quiz_attempts`**
- `selection_meta` JSON, e.g. `{ "recycled": true, "pool_size": 100, "excluded_count": 45, "fresh_available": 5 }`

No new tables; `questions_order` stores the attempt’s question IDs.

## Core Service
`App\Services\Quiz\QuizRandomSelectionService`
- `buildCandidatePool(Quiz)` — direct + pool questions, deduped
- `getPreviouslyUsedQuestionIds(Quiz, studentId)` — from completed attempts
- `selectForAttempt(Quiz, studentId)` → `QuizRandomSelectionResult`
- `estimateMaxScore(Quiz)` — top-N grades for admin display
- `validateQuizConfiguration(Quiz)` — empty bank / N > pool size

## Start Flow
`QuizAttemptStartService::prepareStart()` used by:
- `QuizAttemptController::start()` (web)
- `QuizApiController::start()` (API)
- `QuizPreviewFlowService` (admin preview; no cross-attempt exclusion)

Creates `QuizResponse` rows only for selected questions; pool-sourced questions resolve grades via candidate pool.

## Admin UI
- Create/edit: type «بنك عشوائي», field `questions_per_attempt`
- Manage questions: stats bar (pool size, N, validation), attach/detach question pools via AJAX
- Index: filter + badge; questions column shows `N / pool_size`

## Student UI
- Quiz intro: «X questions from bank of Y»
- Take page: warning banner when `pool_recycled` or `selection_meta.recycled`

## Edge Cases
| Case | Behavior |
|------|----------|
| Empty bank | Block start; admin validation warning |
| N > pool_size | Admin validation; block publish/start |
| In-progress attempt | Resume existing `questions_order` (no re-draw) |
| Preview attempt | Not counted in exclusion set |

## Tests
- Unit: `tests/Unit/Quiz/QuizRandomSelectionServiceTest.php`
- Feature: `tests/Feature/Quiz/RandomPoolQuizAttemptTest.php` — two attempts minimize overlap
