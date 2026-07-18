# Session Activity Tracking — Performance Hardening Design

**Date:** 2026-07-17  
**Status:** Approved (final — 2026-07-17 amendments applied)  
**Scope:** Improve existing session activity tracking; do not remove or rebuild from scratch.

---

## 1. Goals

- Reduce `session_activities` table growth without deleting the table or disabling tracking.
- Prefer UPDATE / skip over INSERT for noisy repeated events.
- Stop expected validation failures from flooding `laravel.log`.
- Keep report accuracy: session start, last activity, duration, and meaningful activities.
- Preserve full backward compatibility of existing APIs and admin UIs.
- Make dedup window configurable via `.env`.

Non-goals:

- Dropping `session_activities` or `user_sessions`.
- Changing route URLs for track/heartbeat.
- Requiring Redis exclusively.
- Allowing the client to end a server session.

---

## 2. Decisions (locked)

| Decision | Choice |
|----------|--------|
| Dedup policy | Smart by activity type (option C) |
| `page_view` source | Middleware only (option B); remove from JS/client |
| Page close signal | Client sends `disconnect` only; never `session_end` |
| Dedup store | Cache first, DB only when needed (option C) |
| Cache driver | Default Laravel `Cache` facade (Redis / file / database — no code changes per driver) |
| Always-insert events | `action` plus extensible learning events |
| Real session end | Server only (logout, timeout, forced disconnect policies) |
| Column type | Keep MySQL `ENUM`; expand via migration for new values |
| Cache payload | No `activity_id` — only `occurred_at`, `last_type`, `last_hash` |

---

## 3. Activity type taxonomy

### 3.1 Server-owned (never accepted from client track API)

- `session_start` — created in `SessionTrackingService::startSession` on login.
- `session_end` — created in `SessionTrackingService::endSession` on logout / server-side end.

### 3.2 Middleware-owned

- `page_view` — recorded only by `SessionTrackingMiddleware` for full HTML navigations (existing filters: not ajax/json/assets/api).

### 3.3 Client-owned (track API)

Accepted by `/api/session/track`:

| Type | Dedup behavior |
|------|----------------|
| `focus_lost` | Skip if same type for same session within window |
| `focus_gained` | Skip if same type for same session within window |
| `disconnect` | Skip if same type for same session within window |
| `reconnect` | Skip if same type for same session within window |
| `idle_start` | UPDATE latest same-type row within window; else INSERT |
| `idle_end` | UPDATE latest same-type row within window; else INSERT |
| `action` | Always INSERT |
| Learning events (future-ready) | Always INSERT |

### 3.4 Always-insert learning events (extensible allow-list)

These are always recorded (no dedup), even if unused today:

- `lesson_open`
- `lesson_complete`
- `video_start`
- `video_complete`
- `quiz_start`
- `quiz_submit`
- `file_download`

Implementation note: keep a single allow-list in config (e.g. `config/session_tracking.php`) used by validation and recorder. Adding a new learning type later = config + **ENUM expansion migration**.

### 3.5 Database enum compatibility (locked)

**Keep `activity_type` as MySQL ENUM.** Do not convert to string.

Current values:  
`session_start`, `session_end`, `page_view`, `action`, `disconnect`, `reconnect`, `idle_start`, `idle_end`, `focus_lost`, `focus_gained`.

This work adds a migration that **expands the ENUM** to include:

`lesson_open`, `lesson_complete`, `video_start`, `video_complete`, `quiz_start`, `quiz_submit`, `file_download`.

Reasons for keeping ENUM:

- Values are known and finite.
- Slightly better storage/indexing characteristics for MySQL.
- Rejects invalid values at the database layer.
- Future types require an intentional migration (safer than free-form strings).

---

## 4. Dedup + Cache algorithm

### 4.1 Config

```env
SESSION_ACTIVITY_TRACKING_ENABLED=true
SESSION_ACTIVITY_DEDUP_SECONDS=30
```

```php
// config/session_tracking.php
return [
    'enabled' => env('SESSION_ACTIVITY_TRACKING_ENABLED', true),
    'dedup_seconds' => (int) env('SESSION_ACTIVITY_DEDUP_SECONDS', 30),
    'cache_ttl_seconds' => (int) env('SESSION_ACTIVITY_DEDUP_SECONDS', 30) + 5,
    'skip_if_recent' => ['focus_lost', 'focus_gained', 'disconnect', 'reconnect'],
    'update_if_recent' => ['idle_start', 'idle_end'],
    'always_insert' => [
        'action',
        'lesson_open', 'lesson_complete',
        'video_start', 'video_complete',
        'quiz_start', 'quiz_submit',
        'file_download',
    ],
    'client_allowed' => [ /* union of skip + update + always_insert */ ],
    'server_only' => ['session_start', 'session_end'],
    'middleware_only' => ['page_view'],
];
```

### 4.2 Cache key / value (no activity_id)

- Key: `session_activity:last:{userSessionId}:{activityType}`
- Value (JSON-serializable array only):

```json
{
  "occurred_at": "2026-07-17T19:00:00+00:00",
  "last_type": "focus_lost",
  "last_hash": "sha1-of-normalized-details-or-empty"
}
```

- Do **not** store `activity_id` in cache. Cache must not be tightly coupled to DB row identity.
- TTL: `cache_ttl_seconds` (dedup + 5)
- Driver: Laravel `Cache` facade (Redis / file / database via app config — no driver-specific code)

`last_hash` is a short hash of normalized `activity_details` + `page_url` (optional aid for “same event” checks). For skip/update rules keyed primarily by type + time window, hash may be empty for simple focus/disconnect events.

### 4.3 Flow for `trackActivity`

1. If tracking disabled → return null / no-op success.
2. Classify type via config lists.
3. If `always_insert` → INSERT, then write cache payload (no id), return activity.
4. If `skip_if_recent` or `update_if_recent`:
   - Read cache payload.
   - If cache hit and age < `dedup_seconds`:
     - skip types → return success with `skipped: true` and **no DB write**.
     - update types → **query DB once** for latest row of this session+type, UPDATE `occurred_at` / details, then refresh cache (still without storing id in cache after write — or store only timestamps/hash).
   - If cache miss:
     - Query DB once for latest matching row; if recent enough, apply skip/update as above and refill cache; else INSERT and refill cache.
5. Validation / expected paths never call `Log::error`.

Heartbeat remains UPDATE-only on `user_sessions` (touch), no activity row.

---

## 5. Logging policy (locked)

| Case | HTTP | Log |
|------|------|-----|
| Unauthenticated | 401 | none |
| No active session | 400 | none |
| `ValidationException` / disallowed type | 422 | **none** (never `Log::error`, never stack trace) |
| Expected skip (dedup) | 200 `{success:true, skipped:true}` | none |
| Unexpected exception | 500 | **`Log::error`** for real failures (message + context; stack only when `app.debug`) |
| Unknown `activity_type` repeated abnormally | 422 | **`Log::warning` rate-limited** — e.g. at most once per minute per type (or after > X occurrences / minute via cache counter). Not per request. |

Controller changes:

- Do not wrap validation in a generic `\Exception` catch that logs errors.
- Let Laravel return 422 for validation, or catch `ValidationException` and return JSON without logging.
- Keep `Log::error` for unexpected exceptions only.
- For unknown types: increment a short-lived cache counter; when threshold exceeded, emit one `Log::warning` then silence until the window resets.

---

## 6. Client / frontend changes

In `admin/layouts/master.blade.php` and `frontend/layouts/master.blade.php` (and any duplicate track scripts):

- Remove client `page_view` POSTs (initial + MutationObserver/popstate page_view).
- Remove `session_end` on `beforeunload` / `sendBeacon`.
- On page hide/unload: send `disconnect` once (beacon/fetch keepalive) respecting dedup.
- Prefer a single focus source: `visibilitychange` **or** blur/focus, not both for the same transition.
- Keep: heartbeat (30s), idle_start/idle_end, reconnect when returning if desired.
- Do not end Laravel session from client events.

API contract remains:

- `POST /api/session/track`
- `POST /api/session/heartbeat`

Responses stay JSON `{ success: bool, ... }` with optional `skipped`, `activity_id`.

---

## 7. Middleware

- Keep `page_view` tracking here as single source of truth.
- Apply same recorder path (so page_view can use mild skip if middleware somehow double-fires within window — optional; primary fix is removing JS duplicates).
- Continue existing exclusions (ajax, json, assets, api).

---

## 8. Reports / admin compatibility

- Admin `user-sessions` UIs continue reading `session_activities`.
- Labels: add new learning types to show blade when column supports them.
- Session duration / last activity still from `user_sessions`.
- Fewer noisy rows; important rows remain queryable.

---

## 9. Testing plan

Feature/unit tests (RefreshDatabase or DatabaseTransactions as project convention):

1. Skip: second `focus_lost` within window does not INSERT.
2. Update: second `idle_start` within window UPDATEs same id.
3. Always insert: two `action` (or `quiz_submit`) rows both INSERT.
4. Client posting `session_end` → 422, no ERROR log assertion where feasible.
5. Client posting `disconnect` → 200, creates or skips per rules.
6. Heartbeat still only touches `user_sessions`.
7. Cache miss then hit path works with `array`/`file` cache in tests.

---

## 10. Rollout / compatibility constraints

- No table drops; no data deletes required for deploy.
- Migration: **expand ENUM** only (add learning event values); keep existing rows intact.
- Minimal file touch set: service, controller, config, middleware (light), layouts JS, migration, tests.
- Feature flag `SESSION_ACTIVITY_TRACKING_ENABLED` allows emergency off without deploy of logic removal.
- Implement in phases with tests green before moving to the next phase.

---

## 11. Out of scope for this pass

- Rewriting admin statistics dashboards.
- Historical backfill/cleanup command for old noisy rows (may be a follow-up).
- Changing heartbeat interval (keep 30s unless separately requested).
