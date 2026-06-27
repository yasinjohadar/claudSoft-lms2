# Google Marketing Analytics Integration

**Date:** 2026-06-20  
**Status:** Implemented

## Overview

Integrates Google Tag Manager (GTM), Search Console verification, GA4 via GTM, and an admin marketing analytics dashboard powered by GA4 Data API and Search Console API—with aggressive caching to avoid performance impact on public pages.

## Architecture

### Public site (visitors)

- Single async GTM snippet via Blade partials on all public layouts (`frontend2`, group registration, legacy frontend).
- Search Console meta verification tag in `<head>`.
- Meta Pixel remains separate (server-side CAPI).
- `dataLayer` pushes for `generate_lead` (diploma registration) and `contact` (contact form).
- No Google API calls on visitor pages.

### Admin

- **Settings:** `/admin/google-settings/edit` — GTM, GSC, API credentials.
- **Dashboard:** `/admin/marketing-analytics` — lazy-loaded JSON + ApexCharts.
- **Cache:** Configurable TTL (default 60 min), hourly pre-warm via `google-analytics:sync`.

## Key files

| File | Purpose |
|------|---------|
| `app/Models/GoogleSetting.php` | Settings + cache |
| `app/Services/Marketing/GoogleDataLayerService.php` | dataLayer events |
| `app/Services/Marketing/GoogleAnalyticsDataClient.php` | GA4 Data API |
| `app/Services/Marketing/GoogleSearchConsoleClient.php` | GSC API |
| `app/Services/Marketing/GoogleServiceAccountTokenService.php` | OAuth JWT token |
| `app/Services/Marketing/MarketingAnalyticsService.php` | Cache + aggregation |
| `resources/views/admin/pages/marketing-analytics/index.blade.php` | Dashboard |

## Manual setup checklist

### 1. Database

```bash
php artisan migrate --path=database/migrations/2026_06_21_140000_add_analytics_api_fields_to_google_settings_table.php
```

### 2. Google Tag Manager

1. Create container at [tagmanager.google.com](https://tagmanager.google.com/)
2. Copy `GTM-XXXXXXX` → Admin → Google Settings
3. Add **GA4 Configuration** tag + **All Pages** trigger
4. (Optional) Custom event tags for `generate_lead` and `contact` from dataLayer

### 3. Search Console

1. Add property at [search.google.com/search-console](https://search.google.com/search-console)
2. Copy HTML verification code → Admin → Google Settings
3. Submit sitemap: `{APP_URL}/sitemap.xml`

### 4. Analytics API (admin dashboard)

1. Google Cloud: enable **Analytics Data API** + **Search Console API**
2. Create Service Account, download JSON
3. Add SA email as **Viewer** on GA4 property
4. Add SA email as **User** on Search Console property
5. Admin → Google Settings: Property ID, Site URL, paste JSON, enable API

### 5. Verification

| Check | How |
|-------|-----|
| GTM loads | DevTools → Network → `gtm.js?id=GTM-...` |
| dataLayer | Console → `window.dataLayer` |
| GA4 realtime | [analytics.google.com](https://analytics.google.com/) → Realtime |
| GSC | Search Console → URL inspection |
| Admin API | `php artisan google-analytics:sync --test` |
| Admin dashboard | `/admin/marketing-analytics` |

## Performance rules

- GTM script is async; one snippet only.
- Settings cached 5 minutes (`GoogleSetting::getSettings`).
- Analytics API data cached 60 minutes (configurable).
- Dashboard loads skeleton first; data via AJAX.
- Refresh button rate-limited to 1/min per admin user.
- Hourly scheduler pre-warms cache.

## Out of scope

- Real-time GA4 API in admin
- Embedded Google iframes
- Tracking student/admin areas
- Meta Pixel via GTM
- BigQuery export
