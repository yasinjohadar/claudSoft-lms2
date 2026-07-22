# App Storage UI Redesign (Approach A)

**Date:** 2026-07-21  
**Status:** Approved direction (Approach A) — awaiting user review of this spec  
**Scope:** All `resources/views/admin/pages/app-storage/*.blade.php` pages

## Goal

Restyle App Storage admin pages to match the established Backup Storage UI pattern (`group-show-hero`, `admin-stats-card`, `group-show-members-card`, Feather icons, breadcrumb, alerts partial) without changing routes, controllers, storage drivers, or business logic.

## Out of scope

- Backend / routes / migrations / models
- Changing inventory migration workflows or AJAX endpoints
- Merging `app-storage` with `backup-storage` (they remain separate modules)
- New features (health dashboards, new drivers, etc.)

## Reference pattern

Mirror structure and class names from:

- `resources/views/admin/pages/backup-storage/index.blade.php`
- `resources/views/admin/pages/backup-storage/create.blade.php`
- `resources/views/admin/pages/backup-storage/edit.blade.php`
- `resources/views/admin/pages/backup-storage/analytics.blade.php`

Shared building blocks already in the admin theme:

- Breadcrumb under `page-header-breadcrumb`
- `@include('admin.components.alerts')`
- `group-show-hero` + `group-show-actions`
- `admin-stats-card` (+ color modifiers)
- `group-show-members-card` for tables/forms
- Icons: prefer `fe fe-*` for chrome; keep existing functional icons where JS depends on markup

## Pages and intended chrome

| Page | File | Chrome | Body |
|------|------|--------|------|
| Index | `index.blade.php` | Hero + 3 stats (total / active / inactive) + actions (add, inventory) | Hover table; keep test-connection fetch JS |
| Create | `create.blade.php` | Hero + back action | Form inside members-card; keep driver field JS |
| Edit | `edit.blade.php` | Hero with config name + back | Same as create; keep update/test JS |
| Analytics | `analytics.blade.php` | Hero + back to index | Filters + results in members-card(s); keep budget alert |
| Inventory | `inventory.blade.php` | Replace custom `.inv-hero` with `group-show-hero`; map key counters to `admin-stats-card` where straightforward | Keep inventory tables, flow UI, and page-specific JS/CSS needed for behavior |
| Cloud files | `cloud-files.blade.php` | Replace `.cf-hero` with `group-show-hero`; stats → `admin-stats-card` when 1:1 | Keep browser table, breadcrumbs of path, and JS |
| Local files | `local-files.blade.php` | Same treatment as cloud | Keep local file management behavior |
| Browse local | `browse-local.blade.php` | Same shell if it has a custom hero | Keep browse behavior |
| Partial | `partials/capacity-summary.blade.php` | Align classes to stats cards only if used by redesigned parents | No logic change |

## Index specifics

- Stats: use real Blade values (not broken `data-countup` with literal `0` unless countup script is included on the page). Prefer direct `{{ $count }}` like fixed backup-schedules index.
- Actions: إضافة مكان تخزين → `app-storage.configs.create`; جرد الملفات → `app-storage.inventory.index`; optional link to analytics if route exists.
- Preserve test button + CSRF fetch to `/admin/app-storage/configs/{id}/test` (or named route if already used).
- Driver labels via `AppStorageConfig::DRIVERS`.

## Create / Edit specifics

- Keep all existing form fields, `id="storage-form"`, `#config-fields` dynamic fill, and test-connection UI.
- Wrap form in `group-show-members-card`.
- Use Feather for chrome buttons; do not rename field `name` attributes.

## Inventory / browse specifics

- Priority: visual shell consistency first.
- Do **not** rewrite inventory algorithms, status badges logic, or migration buttons.
- Remove or slim page-local hero CSS only when replaced by shared classes; leave behavioral CSS (tables, flow steps, notes) intact.
- If a full CSS rewrite would risk breakage, keep specialized CSS for the interactive body.

## Success criteria

1. Opening each App Storage page shows the same visual language as Backup Storage.
2. Create/edit/test/delete/inventory/browse actions still work unchanged.
3. No PHP controller or route changes required (Blade-only unless a tiny view-variable is already available).
4. Mobile-friendly: hero actions wrap; tables remain `table-responsive`.

## Implementation notes

- Views only under `resources/views/admin/pages/app-storage/`.
- Prefer copying structure from backup-storage then swapping route names to `app-storage.*`.
- Avoid introducing new CSS files unless a small shared adjustment is already used by backups.
- After UI pass, smoke-check: index list, test connection, open create, open inventory, open analytics.

## Non-goals / explicit decisions

- **Approach A only** (not B full CSS rewrite, not C chrome-only).
- Stats on index: direct numbers, not countup.
- Modules stay separate: App Storage ≠ Backup Storage.
