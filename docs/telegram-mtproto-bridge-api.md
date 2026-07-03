# Telegram MTProto Bridge (External Service)

Laravel communicates with this service via `TelegramBridgeClient`.

## Expected HTTP API

| Method | Path | Body/Query | Response |
|--------|------|------------|----------|
| GET | `/health` | — | `{ "status": "ok" }` |
| GET | `/groups` | `account_id?` | `{ "groups": [{ "id", "title", "chat_id" }] }` |
| GET | `/groups/{chatId}/members` | `account_id?` | `{ "members": [{ "id", "username" }] }` |
| POST | `/groups/create` | `{ "title", "account_id?" }` | `{ "chat_id", "invite_link?" }` |
| POST | `/groups/{chatId}/add-bot` | `{ "bot_username" }` | `{ "ok": true }` |

Implement with Python (Telethon) or Node (GramJS). Host beside Evolution on Coolify/Docker.

Configure in LMS: **Admin → Telegram → Settings → Bridge Base URL**.
