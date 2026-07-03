# WhatsApp AI Auto-Reply (Evolution Support Instance)

**Date:** 2026-06-20  
**Status:** Implemented

## Summary

AI-powered WhatsApp auto-reply for a **single Evolution support instance**, with FAQ-only responses (no student PII), human-like delivery (initial delay, typing presence, message splitting), debounce, and contact cooldown.

## Architecture

```
Evolution Webhook → ProcessWhatsAppWebhookEventJob (stores instance on message)
  → WhatsAppMessageReceived → AutoReplyWhatsAppListener
  → WhatsAppAutoReplyService::scheduleForReply (debounce)
  → ProcessWhatsAppAutoReplyJob (delayed)
  → WhatsAppAutoReplyService::processContact
      → AI generate (FAQ prompt) → Humanizer split
      → Presence composing → waitBeforeSend → sendTextSync (same instance)
```

## Settings (`whatsapp` group)

| Key | Purpose |
|-----|---------|
| `auto_reply_evolution_instance` | Support instance name |
| `auto_reply_faq_context` | FAQ text for AI |
| `auto_reply_initial_delay_min/max` | Reading delay (seconds) |
| `auto_reply_typing_duration` | Typing indicator duration |
| `auto_reply_max_chunks` / `auto_reply_chunk_max_chars` | Message splitting |
| `auto_reply_contact_cooldown` | Per-contact reply cooldown |
| `auto_reply_debounce_seconds` | Merge rapid messages |
| `auto_reply_test_phone` | Admin test number |

## Admin UI

- [`resources/views/admin/pages/whatsapp-settings/index.blade.php`](resources/views/admin/pages/whatsapp-settings/index.blade.php)
- Preview: `POST admin/whatsapp-settings/auto-reply/preview`
- Test send: `POST admin/whatsapp-settings/auto-reply/test-send`

## Key Classes

- `App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyService`
- `App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyPromptBuilder`
- `App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyHumanizer`
- `App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyAiGenerator`
- `App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyPresenceService`
- `App\Jobs\ProcessWhatsAppAutoReplyJob`

## Operational Requirements

1. WhatsApp enabled, provider = Evolution
2. Support instance selected in settings
3. Webhook `MESSAGES_UPSERT` configured
4. `php artisan queue:work` running

## Anti-ban Measures

- Single support instance (no rotation on replies)
- Debounce for burst messages
- Contact cooldown
- Per-instance send delays + optional random variation
- Typing simulation before each chunk
