# App Storage UI Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle all App Storage admin Blade views to match Backup Storage chrome (`group-show-hero`, `admin-stats-card`, `group-show-members-card`) without changing routes, controllers, or storage logic.

**Architecture:** Blade-only UI pass. CRUD/analytics pages are rewritten to mirror `resources/views/admin/pages/backup-storage/*` with `app-storage.*` route names. Inventory/browse pages keep behavioral CSS/JS; only hero/stats shells are swapped to shared classes. No PHP backend changes.

**Tech Stack:** Laravel Blade, existing admin theme classes (`group-show-*`, `admin-stats-card`, Feather `fe` icons), Bootstrap 5 RTL admin layout.

**Spec:** `docs/superpowers/specs/2026-07-21-app-storage-ui-redesign-design.md`

**Commits:** Do **not** commit unless the user explicitly asks. Skip commit steps during execution.

---

## File map

| File | Responsibility |
|------|----------------|
| `resources/views/admin/pages/app-storage/index.blade.php` | Config list + stats + test JS |
| `resources/views/admin/pages/app-storage/create.blade.php` | New config form chrome |
| `resources/views/admin/pages/app-storage/edit.blade.php` | Edit config form chrome |
| `resources/views/admin/pages/app-storage/analytics.blade.php` | Analytics filters + stats |
| `resources/views/admin/pages/app-storage/inventory.blade.php` | Inventory hero/stats shell only |
| `resources/views/admin/pages/app-storage/cloud-files.blade.php` | Cloud browser hero/stats shell |
| `resources/views/admin/pages/app-storage/local-files.blade.php` | Local files hero/stats shell |
| `resources/views/admin/pages/app-storage/browse-local.blade.php` | Browse-local hero/stats shell |
| `resources/views/admin/pages/app-storage/partials/capacity-summary.blade.php` | Optional visual alignment only |

**Reference (read-only):**

- `resources/views/admin/pages/backup-storage/index.blade.php`
- `resources/views/admin/pages/backup-storage/create.blade.php`
- `resources/views/admin/pages/backup-storage/edit.blade.php`
- `resources/views/admin/pages/backup-storage/analytics.blade.php`

**Route names to preserve:**

- `app-storage.configs.index|create|store|edit|update|destroy|test`
- `app-storage.analytics`
- `app-storage.inventory.*`

---

### Task 1: Redesign configs index

**Files:**
- Modify: `resources/views/admin/pages/app-storage/index.blade.php`
- Reference: `resources/views/admin/pages/backup-storage/index.blade.php`

- [ ] **Step 1: Replace the full view with Backup Storage–style chrome**

Rewrite `index.blade.php` so it contains:

1. Breadcrumb: لوحة التحكم → إعدادات التخزين  
2. `@include('admin.components.alerts')`  
3. `group-show-hero` with title «إعدادات التخزين» and actions:
   - primary → `route('app-storage.configs.create')` «إضافة مكان تخزين»
   - success → `route('app-storage.analytics')` «التحليلات»
   - info → `route('app-storage.inventory.index')` «جرد الملفات»
4. Three `admin-stats-card` values using **direct numbers** (not `data-countup` literal 0):

```blade
@php
    $activeCount = $configs->where('is_active', true)->count();
    $inactiveCount = $configs->where('is_active', false)->count();
@endphp
{{-- ... --}}
<h3 class="admin-stats-card__value mb-0">{{ $configs->count() }}</h3>
{{-- active / inactive similarly --}}
```

5. Table inside `group-show-members-card`, columns unchanged (#, الاسم, النوع, الحالة, الأولوية, الإجراءات).  
6. Driver badge: `App\Models\AppStorageConfig::DRIVERS[$config->driver] ?? $config->driver` with `badge bg-info-transparent text-info`.  
7. Actions: edit (`fe-edit-2`), test (`fe-zap` + form id `test-form-{{ id }}` posting to `route('app-storage.configs.test', $config->id)`), delete (`fe-trash-2`).  
8. Keep fetch-based test script; use `form.action` like backup-storage index (not hard-coded URL).

- [ ] **Step 2: Smoke-check index markup**

Run:

```bash
php artisan view:cache
```

Expected: success (no Blade compile errors). If `view:cache` is unavailable in env, open `/admin/app-storage/configs` and confirm hero + non-zero stats when configs exist.

- [ ] **Step 3: Skip commit** (unless user asked)

---

### Task 2: Redesign create + edit

**Files:**
- Modify: `resources/views/admin/pages/app-storage/create.blade.php`
- Modify: `resources/views/admin/pages/app-storage/edit.blade.php`
- Reference: `resources/views/admin/pages/backup-storage/create.blade.php`, `edit.blade.php`

- [ ] **Step 1: Restyle create chrome; keep form fields and scripts**

Replace the old `page-title` header + bare card with:

1. Breadcrumb → configs index → إضافة  
2. Alerts + validation errors (Feather alert icon)  
3. `group-show-hero` («مكان تخزين جديد») + single back action to `app-storage.configs.index`  
4. Wrap existing `<form id="storage-form" action="{{ route('app-storage.configs.store') }}">` … fields … inside `group-show-members-card` titled «إعدادات الاتصال»  
5. **Do not** rename `name` attributes, `#config-fields`, `#driver`, `#test-connection-result`, or `@push('scripts')` driver/config JS  
6. Prefer `fe` icons on chrome buttons; leave Font Awesome inside existing JS strings if already used

- [ ] **Step 2: Restyle edit the same way**

Same shell as create, with:

- Title from `{{ $config->name }}`  
- Form `route('app-storage.configs.update', $config->id)` + `@method('PUT')`  
- Preserve all existing config field population and test-connection JS

- [ ] **Step 3: Compile views**

```bash
php artisan view:cache
```

Expected: success.

- [ ] **Step 4: Skip commit**

---

### Task 3: Redesign analytics

**Files:**
- Modify: `resources/views/admin/pages/app-storage/analytics.blade.php`
- Reference: `resources/views/admin/pages/backup-storage/analytics.blade.php`

- [ ] **Step 1: Apply Backup analytics layout with App Storage routes/models**

Structure:

1. Breadcrumb → configs index → التحليلات  
2. Budget alert if `$budgetAlert` set  
3. `group-show-hero` + back to `app-storage.configs.index`  
4. Filter form in `group-show-members-card` → `route('app-storage.analytics')`  
5. Driver label: `App\Models\AppStorageConfig::DRIVERS[...]` (**not** `BackupStorageConfig`)  
6. When `$selectedConfig && $stats`, show the four `admin-stats-card` GB/cost cards and the two secondary members-cards (daily average cost, operations) using the same `$stats` keys already used in the current analytics view

Preserve existing variable names from `AppStorageAnalyticsController` (`$configs`, `$period`, `$selectedConfig`, `$stats`, `$budgetAlert`). If a key differs from backup-storage, keep the **app-storage** keys already in the current file.

- [ ] **Step 2: Compile views**

```bash
php artisan view:cache
```

Expected: success.

- [ ] **Step 3: Skip commit**

---

### Task 4: Inventory hero + stats shell

**Files:**
- Modify: `resources/views/admin/pages/app-storage/inventory.blade.php` (hero/stats region only; ~lines 151–250 area)

- [ ] **Step 1: Replace `.inv-hero` with shared chrome**

After breadcrumb (add one if missing) and before `.inv-note` / flow:

1. Insert standard breadcrumb: لوحة التحكم → إعدادات التخزين (`configs.index`) → جرد الملفات  
2. `@include('admin.components.alerts')` if not already present  
3. Replace `<div class="inv-hero">...</div>` with `group-show-hero`:
   - Title: جرد الملفات والترحيل  
   - Desc: last scan text (reuse `$hasScan` / `$scannedAt` logic)  
   - Actions as `group-show-action` links to: cloud-files, browse-local, local-files, configs.index, storage-disk-mappings.index  
4. Where `.inv-stat` summary counters exist at the top, convert each to `admin-stats-card` (blue/green/orange/cyan as appropriate) **without** changing linked filter URLs or counts  
5. Keep `.inv-page` wrapper and all inventory-specific CSS for flow/table/selection-bar  
6. Remove unused `.inv-hero` CSS rules from `@section('styles')` only after markup no longer references them; leave `.inv-stat` CSS if still used deeper in the page

- [ ] **Step 2: Verify inventory JS selectors still match**

Confirm `@push('scripts')` still finds selection bar / progress fetch URLs unchanged (`app-storage.inventory.progress`, migrate forms, etc.).

- [ ] **Step 3: Compile views**

```bash
php artisan view:cache
```

Expected: success.

- [ ] **Step 4: Skip commit**

---

### Task 5: Cloud / local / browse-local shells

**Files:**
- Modify: `resources/views/admin/pages/app-storage/cloud-files.blade.php`
- Modify: `resources/views/admin/pages/app-storage/local-files.blade.php`
- Modify: `resources/views/admin/pages/app-storage/browse-local.blade.php`

- [ ] **Step 1: Cloud files — swap `.cf-hero`**

Replace `.cf-hero` block with `group-show-hero` + breadcrumb (configs → inventory → استعراض السحابة). Move hero action buttons (inventory, local-files, browse-local) into `group-show-actions`. Convert top `.cf-stat` row to `admin-stats-card` if values are simple counters. Keep path breadcrumb UI, table, and all `route('app-storage.inventory.cloud-files'...)` links. Leave `.cf-page` behavioral CSS.

- [ ] **Step 2: Local files — swap `.lf-hero`**

Same pattern: hero + actions (browse-local, cloud-files, inventory, scan form). Keep delete/filter/selection-bar JS and forms. Convert top `.lf-stat` to `admin-stats-card` when 1:1.

- [ ] **Step 3: Browse local — swap `.bl-hero`**

Same pattern: hero + actions. Keep directory listing and `$routeParams` navigation.

- [ ] **Step 4: Compile all three**

```bash
php artisan view:cache
```

Expected: success.

- [ ] **Step 5: Skip commit**

---

### Task 6: Capacity partial (light touch)

**Files:**
- Modify: `resources/views/admin/pages/app-storage/partials/capacity-summary.blade.php`

- [ ] **Step 1: Align outer cards only if low-risk**

If the partial uses custom `.capacity-box` classes that still work visually inside redesigned parents, leave markup as-is except:

- Prefer wrapping the two columns in styling consistent with `admin-stats-card` **only if** it does not break included CSS from parent pages  
- Do **not** change refresh form action `app-storage.inventory.refresh-capacity` or hidden `return` field  

If converting risks layout breakage with parent-scoped CSS, **skip structural changes** and only ensure the partial still renders (YAGNI).

- [ ] **Step 2: Compile views**

```bash
php artisan view:cache
```

- [ ] **Step 3: Skip commit**

---

### Task 7: Final smoke checklist

**Files:** none (manual / artisan)

- [ ] **Step 1: Clear compiled views for local preview**

```bash
php artisan view:clear
```

- [ ] **Step 2: Manual checklist in browser** (admin logged in)

| URL | Check |
|-----|--------|
| `/admin/app-storage/configs` | Hero, stats > 0 when configs exist, edit/test/delete visible |
| `/admin/app-storage/configs/create` | Hero + form fields appear for chosen driver |
| `/admin/app-storage/configs/{id}/edit` | Existing values loaded |
| `/admin/app-storage/analytics` | Filter form works |
| `/admin/app-storage/inventory` | Hero actions + scan/migrate UI intact |
| `/admin/app-storage/inventory/cloud-files` | Listing works |
| `/admin/app-storage/inventory/local-files` | Listing works |
| `/admin/app-storage/inventory/browse-local` | Listing works |

- [ ] **Step 3: Confirm no controller/route diffs**

```bash
git diff --stat -- routes app
```

Expected: no changes under `routes/` or `app/` (views/docs only).

---

## Spec coverage (self-review)

| Spec requirement | Task |
|------------------|------|
| Index hero + stats + table | Task 1 |
| Direct stats numbers (no broken countup) | Task 1 |
| Create/edit members-card + keep JS | Task 2 |
| Analytics pattern | Task 3 |
| Inventory shell only | Task 4 |
| Cloud/local/browse shells | Task 5 |
| Capacity partial light touch | Task 6 |
| No backend changes | Task 7 git check |
| Approach A (not full CSS rewrite) | Tasks 4–5 keep page CSS |

## Placeholder scan

No TBD/TODO left in task steps. Commit steps explicitly skipped per user git rules.
