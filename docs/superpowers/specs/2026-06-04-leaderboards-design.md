# Leaderboards System — Design Spec

## Goal
Fix broken leaderboard backend and redesign admin + student pages to match modern gamification UI.

## Architecture
- `metric` column drives scoring from `user_stats` or `points_transactions` aggregates.
- `period` drives time scope (all_time, weekly, monthly).
- `type` is display/category only.
- Rankings rebuilt via `LeaderboardService::updateLeaderboard()` on schedule + debounced listener.

## Backend Fixes
- Student filter via Spatie roles.
- `metrics` column (not metadata) on entries.
- Division calculation highest-tier-first.
- Previous rank/score preserved on rebuild.
- `rewards` JSON on leaderboards table.
- `UpdateLeaderboardListener` with 60s debounce.

## UI
- Admin: index/create/edit/show matching points admin pattern.
- Student: index/show/division/my-rank matching points history pattern.

## Tests
- LeaderboardServiceTest, AdminLeaderboardTest, StudentLeaderboardPageTest.
