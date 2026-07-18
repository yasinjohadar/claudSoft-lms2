# Session Activity Tracking — Performance Hardening Design

**Date:** 2026-07-17  
**Status:** Approved (with amendments)  
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

Implementation note: keep a single allow-list in config (e.g. `config/session_tracking.php`) used by validation and recorder. Adding a new learning type later = config + DB enum migration when needed.

### 3.5 Database enum compatibility

Current DB enum includes:  
`session_start`, `session_end`, `page_view`, `action`, `disconnect`, `reconnect`, `idle_start`, `idle_end`, `focus_lost`, `focus_gained`.

Learning event values are **not** in the enum yet. Plan:

1. Phase 1 (this work): accept them in application validation/config and map unknown-but-always-insert types that are not yet in DB to `action` **with** original type preserved in `activity_details.event` — **OR** add a migration expanding the enum.

**Preferred for correctness and reports:** add a migration that expands `activity_type` enum (or converts column to `string` indexed) to include the learning events. Prefer converting `activity_type` from enum to `string(64)` + index for future extensibility without repeated ALTER ENUM. Existing values remain valid. This is still “keeping the table,” only improving the column type.

If converting enum → string is considered too invasive for this pass, expand the MySQL enum list in a migration instead. Spec default: **convert to string(64) + index** for maintainability.

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

### 4.2 Cache key / value

- Key: `session_activity:last:{userSessionId}:{activityType}`
- Value: `['id' => int, 'occurred_at' => iso8601 string]`
- TTL: `cache_ttl_seconds` (dedup + 5)
- Driver: `Cache` facade (whatever `CACHE_STORE` / `CACHE_DRIVER` is)

### 4.3 Flow for `trackActivity`

1. If tracking disabled → return null / no-op success.
2. Classify type via config lists.
3. If `always_insert` → INSERT, then write cache, return activity.
4. If `skip_if_recent` or `update_if_recent`:
   - Read cache.
   - On miss: load latest matching row from DB for this session+type within window (or latest overall if needed); refill cache.
   - If last event age < `dedup_seconds`:
     - skip types → return existing activity (or null with success “skipped”) without DB write.
     - update types → UPDATE `occurred_at` (+ merge details/page_url lightly), refresh cache, return.
   - Else → INSERT, refresh cache.
5. Never throw validation noise into ERROR logs.

Heartbeat remains UPDATE-only on `user_sessions` (touch), no activity row.

---

## 5. Logging policy

| Case | HTTP | Log |
|------|------|-----|
| Unauthenticated | 401 | none |
| No active session | 400 | none (or debug at most) |
| Validation failure (unknown/disallowed type) | 422 | **none** — do not catch as generic Exception with stack |
| Expected skip (dedup) | 200 `{success:true, skipped:true}` | none |
| Unexpected exception | 500 | `Log::error` **without** dumping full trace unless `app.debug` |

Controller changes:

- Use Form Request or explicit validation that returns 422.
- Catch `ValidationException` separately and rethrow / return JSON without `Log::error`.
- Remove broad `catch (\Exception)` that logs every validation as error with `trace`.

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
- Optional migration: enum → string(64) for extensibility (backward-compatible values).
- Minimal file touch set: service, controller, config, middleware (light), layouts JS, migration, tests.
- Feature flag `SESSION_ACTIVITY_TRACKING_ENABLED` allows emergency off without deploy of logic removal.

---

## 11. Out of scope for this pass

- Rewriting admin statistics dashboards.
- Historical backfill/cleanup command for old noisy rows (may be a follow-up).
- Changing heartbeat interval (keep 30s unless separately requested).
