# Notifications Server Notes

This file lists what to copy to production server for realtime notifications (Reverb) and mobile push (FCM).

## 1) Required `.env` values on server

Use real production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret

# Reverb internal bind
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_SERVER_PATH=

# Public host used by web clients
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

NOTIFY_CHANNEL_DATABASE=true
NOTIFY_CHANNEL_REALTIME=true
NOTIFY_CHANNEL_FCM=true
NOTIFY_CHANNEL_MAIL=true
NOTIFY_CHANNEL_WHATSAPP=true

FCM_ENABLED=true
FCM_SERVER_KEY=your_fcm_server_key
FCM_ENDPOINT=https://fcm.googleapis.com/fcm/send
FCM_TIMEOUT=20
```

## 2) Start required workers/services

Run all these processes in production (Supervisor/systemd):

1. App web process (Nginx/Apache + PHP-FPM)
2. Queue worker:
   - `php artisan queue:work --tries=3 --timeout=120`
3. Reverb server:
   - `php artisan reverb:start --host=0.0.0.0 --port=8080`

## 3) Build frontend assets after deploy

```bash
npm ci
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4) Reverse proxy rules

Your web server must proxy websocket traffic to Reverb (port 8080).

- Public websocket endpoint should be served over HTTPS/WSS.
- Keep `/broadcasting/auth` accessible behind normal app auth session.

## 5) One-time database setup

```bash
php artisan migrate --force
php artisan db:seed --class=NotificationTemplateSeeder --force
```

## 6) Quick validation checklist

1. Login as student in browser.
2. Trigger an event (quiz start, lesson complete, assignment submit).
3. Confirm:
   - New row appears in `notifications` table.
   - Realtime toast appears without page refresh.
   - `notification_delivery_logs` has `realtime` and/or `fcm` entries.

## 7) Important notes

- Keep `REVERB_APP_SECRET` and `FCM_SERVER_KEY` secret.
- Do not commit production secrets to git.
- If websocket fails behind proxy, verify upgrade headers and WSS forwarding first.
