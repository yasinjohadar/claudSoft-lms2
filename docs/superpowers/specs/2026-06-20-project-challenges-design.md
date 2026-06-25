# Project Challenges — Design Spec

## Goal
Standalone team-based project challenge system (not tied to courses). Multiple teams per challenge; each team executes its own project through staged link submissions, grading with progress %, final showcase publishing, and platform-wide comments.

## Distinction
| System | Tables | Routes |
|--------|--------|--------|
| Programming Challenge | `programming_challenges_*` | `student/challenges/*` |
| Gamification Challenge | `challenges`, `user_challenges` | `gamification/challenges/*` |
| **Project Challenge** | `project_*` | `student/project-challenges/*` |

## Core Entities
- `project_challenges` — challenge container
- `project_stages` — ordered stages (shared template)
- `project_teams` + `project_team_members` — teams per challenge
- `project_team_join_requests` + `project_team_invitations` — hybrid team formation
- `project_stage_submissions` + `project_submission_links` — per-team stage deliverables
- `project_showcases` — published final work
- `project_comments` + `project_comment_likes` — community interaction
- `project_activities` — team timeline
- `project_skills` / `project_technologies` — taxonomy

## Team Formation (Hybrid)
- Student creates team or requests join; leader and/or admin approves per `team_approval_mode`.
- Each team has separate project execution under same challenge brief.

## Progress
Weighted: `sum(score/max_score * weight) / sum(weights) * 100` → `project_teams.progress_percent`.

## Showcase
Enabled when mandatory stages approved and progress >= `showcase_threshold`. Published to `/student/community-projects`.

## Notifications
Via `NotificationHubService` event keys: `project.team.*`, `project.stage.*`, `project.showcase.*`, `project.comment.*`.

## Phase 1 MVP
Admin CRUD + stages + grading; student teams + workspace + link submission; community showcase + comments.

## Deferred (Phase 2+)
Rubrics, version history, gamification hooks, AI, PDF reports, optional course reference.
