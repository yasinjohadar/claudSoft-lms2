# Telegram Integration — Hybrid Design Spec

**Date:** 2026-06-20  
**Status:** Approved for implementation  
**Model:** Hybrid (Bot API + MTProto Bridge)

## Overview

Add Telegram as a parallel messaging channel mirroring the WhatsApp stack: student DMs, bulk broadcast, membership invites, group/channel posting, member sync (MTProto), and Notification Hub integration.

## Phases

### Phase 1 — Bot Foundation
- `TelegramSettingsService`, `TelegramBotClient`, `SendTelegramMessage`
- User linking via `/start link_{token}` webhook
- Broadcast + jobs + templates + membership invites
- Admin UI under `/admin/telegram`

### Phase 2 — Groups & Channels
- `telegram_chat_id`, `telegram_group_link` on `group_registration_settings`
- `telegram_channel_links` table
- Semi-auto wizard: admin adds bot to group, links via `/link_group` or paste chat_id
- Post to group/channel from admin

### Phase 3 — MTProto Bridge
- `TelegramAccount` model + `TelegramBridgeClient` (HTTP to external bridge)
- `TelegramGroupCompareService` (mirrors Evolution group compare)
- Auto-create groups via bridge when configured

### Phase 4 — Notification Hub
- `telegram` channel in `notification_hub.php`
- `telegram_enabled` on `notification_user_preferences`
- `TelegramNotificationService` for event-driven sends

## Constraints

- Bot API cannot create supergroups — MTProto bridge required for auto-create
- Bot cannot DM users who haven't `/start` the bot
- MTProto automation carries ToS/ban risk — rate limits mandatory

## Success Criteria

See plan document for acceptance tests per phase.
