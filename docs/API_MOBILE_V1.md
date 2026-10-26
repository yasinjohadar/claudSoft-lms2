# Mobile App API v1 (students only)

Base URL: `{APP_URL}/api/mobile/v1`
Headers: `Accept: application/json` and, after login, `Authorization: Bearer {token}`.

This API is separate from `/api/student/*`, so changes here don't break the existing integrations. The app serves **students only**: any other account gets `403`.

## Response shape

```json
{ "success": true, "message": "…|null", "data": { … }, "meta": { … } }
```

- `meta` appears only on paginated lists: `{current_page, last_page, per_page, total}`.
- Validation errors (422): `{ "message": "…", "errors": { "field": ["…"] } }`.
- 401: the token is invalid or expired. The app should log the user out.
- 403 + `profile_completion_required: true`: the profile is incomplete while completion is enforced. Only the auth/profile/nationalities routes are allowed. The body includes `completion.missing_fields`.

## Endpoints

| Method | Path | Notes |
|---|---|---|
| POST | `auth/login` | `email`, `password`, `device_name?`, `device_token?` (uuid). Throttle: 6/min |
| POST | `auth/logout` | Deletes the current token only |
| GET | `auth/me` | `UserResource` |
| GET | `profile` | `UserResource` |
| PUT | `profile` | `name`, `name_ar`, `country_code`, `phone`, `date_of_birth` (Y-m-d), `gender` (male/female), `city`, `address`, `nationality_id`. Changing the phone is refused (422) when OTP verification is enabled; it must then be changed on the website |
| POST | `profile/avatar` | multipart: `avatar` (image ≤ 2MB) |
| DELETE | `profile/avatar` | |
| PUT | `profile/password` | `new_password`, `new_password_confirmation` (8+ chars, upper and lower case, a number, a symbol). Revokes all other sessions |
| GET | `nationalities` | `[{id, name}]` |
| GET | `courses` | `?status=active\|completed&page=&per_page=` (default 15) |
| GET | `courses/{id}` | 404 if the student isn't enrolled (active/completed) |

## Examples

### `POST auth/login`
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح.",
  "data": {
    "token": "12|xxxx",
    "token_type": "Bearer",
    "user": {
      "id": 5, "name": "Ahmad", "name_ar": "أحمد", "email": "a@x.com", "student_id": "S-001",
      "country_code": "+966", "phone": "512345678", "full_phone": "+966512345678",
      "gender": "male", "date_of_birth": "2000-01-01",
      "nationality": { "id": 3, "name": "سوريا" }, "city": "دمشق", "address": "…",
      "avatar_url": "https://…/profile-photos/…jpg", "has_avatar": true,
      "profile_completion": { "percentage": 100, "missing_fields": [] },
      "last_login_at": "2026-10-26T10:00:00+03:00"
    }
  }
}
```

### `GET courses`
```json
{
  "success": true, "message": null,
  "data": [{
    "id": 12, "slug": "laravel", "title": "Laravel", "short_description": "…",
    "image_url": "https://…", "level": "beginner", "duration_in_hours": 20,
    "instructor": { "id": 2, "name": "م. سامر" },
    "enrollment": { "id": 40, "status": "active", "completion_percentage": 42.5,
      "enrolled_at": "…", "last_accessed_at": "…", "completed_at": null }
  }],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

### `GET courses/{id}`
This returns the same fields as a list item, plus:
```json
{
  "description": "…", "language": "ar",
  "sections_count": 3, "modules_count": 24, "completed_modules_count": 10,
  "sections": [{
    "id": 1, "title": "المقدمة", "description": null,
    "modules_count": 5, "completed_modules_count": 2,
    "modules": [{ "id": 9, "title": "…", "type": "video", "estimated_duration": 12,
      "is_required": true, "status": "completed" }]
  }]
}
```
`status` is one of `not_started | in_progress | completed`. Sections and modules the student can't access are hidden, using the same rules as the student dashboard.

## Tests
```
php artisan test tests/Feature/Api/Mobile
```
The tests run against the local MySQL database inside transactions that roll back. The database name comes from `MOBILE_TEST_DB_DATABASE` (default `cloudsoft_platform`).
