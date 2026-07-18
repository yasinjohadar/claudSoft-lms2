# Session Activity Tracking Hardening — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Harden session activity tracking to cut DB growth and log spam while keeping `session_activities`, APIs, and report accuracy.

**Architecture:** Keep `SessionTrackingService` as the single recorder. Add config-driven type policies (skip / update / always-insert), Cache-first dedup (no activity_id in cache), ENUM expansion migration for learning events, quiet validation logging with rate-limited warnings, Middleware-only `page_view`, client `disconnect` on close.

**Tech Stack:** Laravel Cache facade, MySQL ENUM, existing `SessionActivity` / `UserSession` models, Pest/PHPUnit Feature tests with DatabaseTransactions.

**Spec:** `docs/superpowers/specs/2026-07-17-session-activity-tracking-design.md`

---

## File map

| File | Role |
|------|------|
| `config/session_tracking.php` | Create — allow-lists, dedup seconds, cache TTL |
| `.env.example` | Add `SESSION_ACTIVITY_*` keys |
| `database/migrations/2026_07_17_*_expand_session_activity_types.php` | Expand ENUM |
| `app/Services/SessionTrackingService.php` | Dedup + cache + update paths |
| `app/Http/Controllers/SessionActivityController.php` | Validation allow-list, quiet logs, skip response |
| `app/Http/Middleware/SessionTrackingMiddleware.php` | Keep page_view via service (optional light skip) |
| `resources/views/admin/layouts/master.blade.php` | Remove page_view + session_end; send disconnect |
| `resources/views/frontend/layouts/master.blade.php` | Same |
| `resources/views/admin/user-sessions/show.blade.php` | Labels for new types (if present) |
| `tests/Feature/SessionActivityTrackingTest.php` | Create — core behaviors |

---

### Task 1: Config + ENV

**Files:**
- Create: `config/session_tracking.php`
- Modify: `.env.example`

- [ ] **Step 1: Create config**

```php
<?php

return [
    'enabled' => env('SESSION_ACTIVITY_TRACKING_ENABLED', true),
    'dedup_seconds' => (int) env('SESSION_ACTIVITY_DEDUP_SECONDS', 30),
    'cache_ttl_seconds' => (int) env('SESSION_ACTIVITY_DEDUP_SECONDS', 30) + 5,
    'unknown_type_warning_threshold' => (int) env('SESSION_ACTIVITY_UNKNOWN_WARNING_THRESHOLD', 20),
    'unknown_type_warning_window_seconds' => 60,

    'skip_if_recent' => [
        'focus_lost',
        'focus_gained',
        'disconnect',
        'reconnect',
    ],

    'update_if_recent' => [
        'idle_start',
        'idle_end',
    ],

    'always_insert' => [
        'action',
        'lesson_open',
        'lesson_complete',
        'video_start',
        'video_complete',
        'quiz_start',
        'quiz_submit',
        'file_download',
    ],

    'server_only' => [
        'session_start',
        'session_end',
    ],

    'middleware_only' => [
        'page_view',
    ],
];
```

Add helper in service later: `clientAllowedTypes()` = merge of skip + update + always_insert.

- [ ] **Step 2: Document env keys in `.env.example`**

```
SESSION_ACTIVITY_TRACKING_ENABLED=true
SESSION_ACTIVITY_DEDUP_SECONDS=30
SESSION_ACTIVITY_UNKNOWN_WARNING_THRESHOLD=20
```

- [ ] **Step 3: Verify config loads**

Run: `php artisan config:show session_tracking`

---

### Task 2: ENUM expansion migration

**Files:**
- Create: `database/migrations/2026_07_17_000001_expand_session_activities_activity_type_enum.php`

- [ ] **Step 1: Write migration that ALTERs ENUM to include learning types**

Use MySQL `ALTER TABLE ... MODIFY activity_type ENUM(...)` listing **all old + new** values. Keep index on `activity_type`.

Down migration: reverse to original enum list only if no rows use new values (or leave a safe no-op comment).

- [ ] **Step 2: Run migration on local DB**

Run: `php artisan migrate --path=database/migrations/2026_07_17_000001_expand_session_activities_activity_type_enum.php`

---

### Task 3: Failing tests for dedup / always-insert / validation

**Files:**
- Create: `tests/Feature/SessionActivityTrackingTest.php`

Use project pattern: `Tests\TestCase` + `DatabaseTransactions` + mysql `cloudsoft_platform` like other admin feature tests, **or** RefreshDatabase if suitable. Prefer DatabaseTransactions for speed.

- [ ] **Step 1: Write failing tests**

Cover at minimum:

1. Second `focus_lost` within window → no second INSERT (`assertDatabaseCount` delta 1).
2. Second `idle_start` within window → same row id, `occurred_at` updated.
3. Two `action` events → two rows.
4. Two `quiz_submit` events → two rows.
5. Client `session_end` → 422.
6. Client `disconnect` → 200.
7. Heartbeat does not create `session_activities` row.

Helper: create user, start `UserSession` active, set session `user_session_id`, actingAs.

- [ ] **Step 2: Run tests — expect FAIL**

Run: `php artisan test tests/Feature/SessionActivityTrackingTest.php`

---

### Task 4: Implement Cache-first recorder in SessionTrackingService

**Files:**
- Modify: `app/Services/SessionTrackingService.php`

- [ ] **Step 1: Add cache helpers (no activity_id)**

```php
protected function cacheKey(int $sessionId, string $type): string
{
    return "session_activity:last:{$sessionId}:{$type}";
}

protected function rememberCache(int $sessionId, string $type, ?string $hash = null): void
{
    Cache::put($this->cacheKey($sessionId, $type), [
        'occurred_at' => now()->toIso8601String(),
        'last_type' => $type,
        'last_hash' => $hash ?? '',
    ], config('session_tracking.cache_ttl_seconds'));
}

protected function detailsHash(array $data): string
{
    $normalized = Arr::sortRecursive($data);
    return sha1(json_encode($normalized));
}
```

- [ ] **Step 2: Rewrite `trackActivity` with policies**

Pseudo-order:

1. If `!config('session_tracking.enabled')` return null.
2. Classify against config lists (server_only allowed when called internally).
3. `always_insert` → create + rememberCache + return.
4. For skip/update: read Cache; if miss, load latest DB row for session+type; if recent:
   - skip → return that model (or null) without write; still ensure cache filled from DB timestamps.
   - update → update row `occurred_at` (+ optional details), rememberCache, return.
5. Else INSERT + rememberCache.
6. On unexpected DB failure → `Log::error` (real error).

Important: cache payload never includes `id`. For update path, always `SessionActivity::query()->where(...)->latest('occurred_at')->first()` when update is required.

- [ ] **Step 3: Re-run Task 3 tests — service-level assertions should pass when called directly; HTTP may still fail until Task 5**

---

### Task 5: Harden SessionActivityController

**Files:**
- Modify: `app/Http/Controllers/SessionActivityController.php`

- [ ] **Step 1: Validate against `clientAllowedTypes()` only**

Do not accept `session_start`, `session_end`, `page_view` from client.

- [ ] **Step 2: Fix exception handling**

```php
} catch (ValidationException $e) {
    $this->maybeWarnUnknownActivityType($request->input('activity_type'));
    throw $e; // Laravel → 422 JSON, no Log::error
} catch (\Throwable $e) {
    Log::error('Failed to track activity from frontend', [
        'error' => $e->getMessage(),
        // include trace only if config('app.debug')
    ]);
    return response()->json(['success' => false, 'message' => '...'], 500);
}
```

- [ ] **Step 3: Rate-limited warning helper**

Cache key `session_activity:unknown:{type}` counter; when count crosses threshold within window, one `Log::warning`.

- [ ] **Step 4: Return `{ success: true, skipped: true }` when service signals skip**

Adjust service return to distinguish skip vs insert (e.g. array result or check “no new insert”). Simplest: return `['activity' => ?SessionActivity, 'skipped' => bool]` from a new method `recordActivity` and keep `trackActivity` wrapping for BC, **or** use a lightweight result DTO. Prefer extending return via optional by-ref/`TrackResult` class to avoid breaking callers — check all `trackActivity` callers; most ignore return. Safe approach: add `recordClientActivity` for controller; keep `trackActivity` for internal with policies applied.

- [ ] **Step 5: Re-run feature tests — expect PASS**

---

### Task 6: Frontend / Admin JS cleanup

**Files:**
- Modify: `resources/views/admin/layouts/master.blade.php` (session tracking script ~line 176+)
- Modify: `resources/views/frontend/layouts/master.blade.php` (same pattern)

- [ ] **Step 1: Remove all `trackActivity('page_view'...)` calls** (initial, MutationObserver, popstate)

- [ ] **Step 2: Replace `session_end` unload with `disconnect`**

Use `sendBeacon` / keepalive fetch with `activity_type=disconnect` only. Do not call server session end.

- [ ] **Step 3: Collapse dual focus listeners**

Prefer `document.visibilitychange` only; remove duplicate `window blur/focus` **or** guard with a flag so one logical transition = one event.

- [ ] **Step 4: Keep heartbeat + idle as-is**

- [ ] **Step 5: Manual smoke** — open authenticated page, confirm Network: no page_view from JS; unload sends disconnect; heartbeat continues.

---

### Task 7: Admin labels (compat)

**Files:**
- Modify: `resources/views/admin/user-sessions/show.blade.php` (and styles partial if needed)

- [ ] **Step 1: Add Arabic labels for new learning ENUM values**

---

### Task 8: Final verification

- [ ] **Step 1: Run** `php artisan test tests/Feature/SessionActivityTrackingTest.php`

- [ ] **Step 2: Grep for remaining client `session_end` / JS `page_view` track calls**

Run: `rg "session_end|trackActivity\\('page_view" resources/views`

- [ ] **Step 3: Confirm routes unchanged**

`session.track`, `session.heartbeat` still exist.

---

## Compatibility checklist

- [x] Keep `session_activities` table and data
- [x] Keep ENUM (expand only)
- [x] Cache without activity_id
- [x] Cache via default Laravel driver
- [x] No client session end
- [x] ValidationException not logged as error
- [x] Real errors still Log::error
- [x] Rate-limited Log::warning for unknown types
- [x] APIs `/api/session/track` and `/api/session/heartbeat` unchanged
