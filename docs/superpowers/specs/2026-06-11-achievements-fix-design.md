# Achievements System Fix — Design Spec

**Date:** 2026-06-11  
**Status:** Approved for implementation

## Problem

Admin CRUD writes to `gamification_achievements` while `AchievementService` reads `achievements` + `user_achievements`. Forms use `requirement_type`/`requirement_value`; controller expected legacy fields. Student page variable mismatch. No admin recalculate button.

## Decision

Unify on legacy `App\Models\Achievement` (same pattern as badges). Map admin form fields via `AchievementCriteriaMapper`.

## Components

- `AchievementCriteriaMapper` — form ↔ `criteria.field` + `target_value`
- `UserGamificationStatSyncService` — sync `user_stats` from source tables
- `AchievementRecalculationService` — sync stats + `checkAllAchievements` for all active students
- Admin `AchievementController` — legacy model, mapper on store/update, `recalculateAll` action
- `AchievementService` fixes — stats fields, events, claim status, division guards
- Two recalc buttons: global (points + leaderboards + achievements) and achievements index only

## Requirement mapping

| Form `requirement_type` | `criteria.field` |
|-------------------------|------------------|
| lessons_completed | lessons_completed |
| quizzes_passed | quizzes_completed |
| points_earned | total_points |
| badges_earned | total_badges |
| streak_days | current_streak |
| courses_completed | courses_completed |

## Tests

Mapper unit/feature, admin CRUD, recalculation, student page load.
