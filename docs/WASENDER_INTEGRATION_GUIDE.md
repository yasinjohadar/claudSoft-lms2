# Wasender Integration Guide

This guide covers:
- Fixing recipient format issues (`JID does not exist`) when using `custom_api`.
- Enabling optional preflight validation before sending.
- Connecting Wasender MCP locally, then mirroring the setup on server.

## 1) Custom API settings (Admin panel)

Go to WhatsApp settings and set:
- `whatsapp_provider = custom_api`
- `custom_api_url` = your Wasender send endpoint
- `custom_api_key` = Wasender API key
- `custom_api_method` = `POST`
- Optional: `custom_api_preflight_enabled = true`
- Optional: `custom_api_preflight_url` = endpoint that validates number availability

If preflight endpoint is unavailable/timeouts, send flow continues.  
If preflight explicitly returns number does not exist, sending is blocked with a user-friendly error.

## 2) Accepted recipient formats

### Individual recipient
- International number (digits only or with `+`) is accepted.
- System normalizes to digits for `custom_api` (example: `+966500000000` -> `966500000000`).
- Individual JID is accepted as-is:
  - `xxxxxxxxxxx@s.whatsapp.net`
  - `xxxxxxxxxxx@c.us`

### Group recipient
- Group JID must be valid and is sent as-is:
  - `1234567890-1234567890@g.us`
- Invalid group JID is rejected before API call.

## 3) Troubleshooting common errors

- `The provided JID does not exist on WhatsApp`
  - Cause: invalid `to` format (wrong JID/phone format) or recipient doesn't exist.
  - Action: verify recipient format above and ensure destination exists.

- `Unauthorized` / `Invalid token`
  - Cause: wrong Wasender token.
  - Action: update API key in settings and retest connection.

## 4) Local MCP setup (Cursor/Claude MCP compatible clients)

Use your Personal Access Token and register MCP server:

```bash
claude mcp add --transport http wasenderapi https://wasenderapi.com/mcp \
  --header "Authorization: Bearer YOUR_PERSONAL_ACCESS_TOKEN"
```

Then verify:

```bash
claude mcp list
```

## 5) Advanced Webhook setup (delivery/read + inbound)

Set Wasender webhook URL to:

```text
https://YOUR_DOMAIN/api/webhooks/whatsapp
```

### Security
- Send `Authorization: Bearer <CUSTOM_API_KEY>` in webhook request headers.
- Fallback header is also supported: `X-Webhook-Token: <CUSTOM_API_KEY>`.
- If `custom_api_key` is empty, webhook is accepted (not recommended in production).

### Supported event mapping
- Inbound messages -> stored as `inbound` in `whatsapp_messages`.
- Status updates -> mapped to `sent`, `delivered`, `read`, `failed`.
- Duplicate webhook retries are safely ignored using idempotency (`event_id` hash).

### Notes
- Keep webhook endpoint public over HTTPS.
- The system supports Meta style payloads and Wasender/custom payload variants.
- Full technical details are logged to `whatsapp` log channel; DB keeps clean status fields.

## 6) Production/server MCP checklist

- Keep token in server secrets manager or protected env variable.
- Never commit tokens to repository.
- Use HTTPS only for MCP endpoint.
- Rotate token periodically.
- Run a smoke test call after deployment.

## 7) Quick verification checklist

1. Send individual message using valid number.
2. Send individual message using invalid number (expect friendly validation error).
3. Send group message with valid group JID.
4. Send group message with invalid group JID (expect blocked before provider request).
5. Confirm logs do not expose API keys.
