<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceTrackingService
{
    public const CLIENT_TOKEN_HEADER = 'X-Device-Token';

    public const CLIENT_FINGERPRINT_HEADER = 'X-Device-Fingerprint';

    protected GeoLocationService $geoLocationService;

    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

    /**
     * Track or update device information when user logs in.
     */
    public function trackDeviceOnLogin(User $user, Request $request, bool $trustOnCreate = false): UserDevice
    {
        $fingerprint = $this->generateDeviceFingerprint($request);
        $deviceInfo = $this->detectDeviceInfo($request);
        $ipAddress = $request->ip();

        $device = $this->findDeviceForRequest($user, $request, $fingerprint);

        $location = $this->geoLocationService->getLocationFromIp($ipAddress);

        if ($device) {
            $device->incrementLogins();
            $device->updateLastUsed($ipAddress);

            $meta = $this->mergeClientMeta($device->meta ?? [], $request);
            if ($location) {
                $meta['location'] = $location;
            }

            $updates = [
                'device_fingerprint' => $fingerprint,
                'browser' => $deviceInfo['browser'],
                'browser_version' => $deviceInfo['browser_version'],
                'platform' => $deviceInfo['platform'],
                'platform_version' => $deviceInfo['platform_version'],
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'meta' => $meta,
            ];

            if ($trustOnCreate && ! $device->is_trusted) {
                $updates['is_trusted'] = true;
                $updates['trusted_at'] = now();
                $updates['is_blocked'] = false;
            }

            $device->update($updates);
        } else {
            $meta = $this->mergeClientMeta([], $request);
            if ($location) {
                $meta['location'] = $location;
            }

            $device = UserDevice::create([
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
                'device_name' => null,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'browser_version' => $deviceInfo['browser_version'],
                'platform' => $deviceInfo['platform'],
                'platform_version' => $deviceInfo['platform_version'],
                'ip_address' => $ipAddress,
                'last_ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'total_logins' => 1,
                'first_used_at' => now(),
                'last_used_at' => now(),
                'is_trusted' => $trustOnCreate,
                'trusted_at' => $trustOnCreate ? now() : null,
                'is_blocked' => false,
                'meta' => $meta,
            ]);
        }

        return $device;
    }

    /**
     * Register a pending (untrusted) device when login is denied.
     */
    public function registerPendingDevice(User $user, Request $request): UserDevice
    {
        $fingerprint = $this->generateDeviceFingerprint($request);

        $existing = $this->findDeviceForRequest($user, $request, $fingerprint);

        if ($existing) {
            if ($existing->device_fingerprint !== $fingerprint) {
                $meta = $this->mergeClientMeta($existing->meta ?? [], $request);
                $existing->update([
                    'device_fingerprint' => $fingerprint,
                    'meta' => $meta,
                    'last_used_at' => now(),
                    'last_ip_address' => $request->ip(),
                ]);
            }

            return $existing;
        }

        $deviceInfo = $this->detectDeviceInfo($request);
        $ipAddress = $request->ip();
        $location = $this->geoLocationService->getLocationFromIp($ipAddress);

        $meta = $this->mergeClientMeta([], $request);
        if ($location) {
            $meta['location'] = $location;
        }

        $device = UserDevice::create([
            'user_id' => $user->id,
            'device_fingerprint' => $fingerprint,
            'device_name' => null,
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
            'platform' => $deviceInfo['platform'],
            'platform_version' => $deviceInfo['platform_version'],
            'ip_address' => $ipAddress,
            'last_ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'total_logins' => 0,
            'first_used_at' => now(),
            'last_used_at' => now(),
            'is_trusted' => false,
            'is_blocked' => false,
            'meta' => $meta,
        ]);

        $this->notifyAdminsOfPendingDevice($device);

        return $device;
    }

    /**
     * Resolve device by client token, then client fingerprint, then hashed fingerprint.
     */
    public function findDeviceForRequest(User $user, Request $request, ?string $fingerprint = null): ?UserDevice
    {
        $fingerprint ??= $this->generateDeviceFingerprint($request);

        $clientToken = $this->resolveClientDeviceToken($request);
        if ($clientToken) {
            $byToken = UserDevice::query()
                ->where('user_id', $user->id)
                ->where('meta->client_device_token', $clientToken)
                ->first();

            if ($byToken) {
                $this->syncFingerprintIfNeeded($byToken, $fingerprint, $user->id);

                return $byToken->fresh();
            }
        }

        $clientFp = $this->resolveClientFingerprint($request);
        if ($clientFp) {
            $byFp = UserDevice::query()
                ->where('user_id', $user->id)
                ->where('meta->client_fp', $clientFp)
                ->first();

            if ($byFp) {
                $this->syncFingerprintIfNeeded($byFp, $fingerprint, $user->id);

                return $byFp->fresh();
            }
        }

        return UserDevice::query()
            ->where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->first();
    }

    public function resolveClientDeviceToken(Request $request): ?string
    {
        $token = $request->header(self::CLIENT_TOKEN_HEADER)
            ?? $request->input('device_token')
            ?? $request->cookie('claudsoft_device_token');

        if (! is_string($token) || ! preg_match('/^[a-f0-9-]{36}$/i', $token)) {
            return null;
        }

        return strtolower($token);
    }

    public function resolveClientFingerprint(Request $request): ?string
    {
        $fp = $request->header(self::CLIENT_FINGERPRINT_HEADER)
            ?? $request->input('device_fingerprint_client')
            ?? $request->cookie('claudsoft_device_fp');

        if (! is_string($fp) || ! preg_match('/^[a-f0-9]{32,128}$/i', $fp)) {
            return null;
        }

        return strtolower($fp);
    }

    /**
     * Generate a stable device fingerprint (enriched with client FP / versions when available).
     */
    public function generateDeviceFingerprint(Request $request): string
    {
        $deviceInfo = $this->detectDeviceInfo($request);

        $components = [
            $deviceInfo['device_type'],
            $deviceInfo['browser'],
            $deviceInfo['browser_version'] ?? '',
            $deviceInfo['platform'],
            $deviceInfo['platform_version'] ?? '',
        ];

        if ($clientFp = $this->resolveClientFingerprint($request)) {
            $components[] = 'cfp:'.$clientFp;
        } elseif ($clientToken = $this->resolveClientDeviceToken($request)) {
            $components[] = 'tok:'.$clientToken;
        } else {
            $components[] = $request->header('Accept-Language') ?? 'unknown-lang';
        }

        return hash('sha256', implode('|', $components));
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected function mergeClientMeta(array $meta, Request $request): array
    {
        if ($clientToken = $this->resolveClientDeviceToken($request)) {
            $meta['client_device_token'] = $clientToken;
        }

        if ($clientFp = $this->resolveClientFingerprint($request)) {
            $meta['client_fp'] = $clientFp;
        }

        return $meta;
    }

    protected function syncFingerprintIfNeeded(UserDevice $device, string $fingerprint, int $userId): void
    {
        if ($device->device_fingerprint === $fingerprint) {
            return;
        }

        try {
            $device->update(['device_fingerprint' => $fingerprint]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('Could not update device fingerprint from stable client match', [
                'device_id' => $device->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Detect device information from request.
     */
    public function detectDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';

        $deviceType = $this->detectDeviceType($userAgent);
        $browser = $this->detectBrowser($userAgent);
        $browserVersion = $this->detectBrowserVersion($userAgent, $browser);
        $platform = $this->detectPlatform($userAgent);
        $platformVersion = $this->detectPlatformVersion($userAgent, $platform);

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'platform' => $platform,
            'platform_version' => $platformVersion,
        ];
    }

    protected function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop/i', $userAgent)) {
            if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
                return 'tablet';
            }

            return 'mobile';
        }

        return 'desktop';
    }

    protected function detectBrowser(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/edg/i', $userAgent)) {
            return 'Edge';
        } elseif (preg_match('/chrome/i', $userAgent) && ! preg_match('/edg/i', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/safari/i', $userAgent) && ! preg_match('/chrome/i', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            return 'Opera';
        } elseif (preg_match('/msie|trident/i', $userAgent)) {
            return 'Internet Explorer';
        }

        return 'Unknown';
    }

    protected function detectBrowserVersion(string $userAgent, string $browser): ?string
    {
        $pattern = match (strtolower($browser)) {
            'chrome' => '/chrome\/([0-9.]+)/i',
            'firefox' => '/firefox\/([0-9.]+)/i',
            'safari' => '/version\/([0-9.]+)/i',
            'edge' => '/edg\/([0-9.]+)/i',
            'opera' => '/(?:opera|opr)\/([0-9.]+)/i',
            default => '',
        };

        if ($pattern && preg_match($pattern, $userAgent, $matches)) {
            return $matches[1] ?? null;
        }

        return null;
    }

    protected function detectPlatform(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/windows/i', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return 'iOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        }

        return 'Unknown';
    }

    protected function detectPlatformVersion(string $userAgent, string $platform): ?string
    {
        switch ($platform) {
            case 'Windows':
                if (preg_match('/windows nt ([0-9.]+)/i', $userAgent, $matches)) {
                    $version = $matches[1] ?? null;
                    $versionMap = [
                        '10.0' => '10',
                        '6.3' => '8.1',
                        '6.2' => '8',
                        '6.1' => '7',
                    ];

                    return $versionMap[$version] ?? $version;
                }
                break;
            case 'macOS':
                if (preg_match('/mac os x ([0-9_]+)/i', $userAgent, $matches)) {
                    return str_replace('_', '.', $matches[1] ?? null);
                }
                break;
            case 'Android':
                if (preg_match('/android ([0-9.]+)/i', $userAgent, $matches)) {
                    return $matches[1] ?? null;
                }
                break;
            case 'iOS':
                if (preg_match('/os ([0-9_]+)/i', $userAgent, $matches)) {
                    return str_replace('_', '.', $matches[1] ?? null);
                }
                break;
        }

        return null;
    }

    public function isDeviceBlocked(User $user, Request $request): bool
    {
        $device = $this->findDeviceForRequest($user, $request);

        return $device && $device->is_blocked;
    }

    protected function notifyAdminsOfPendingDevice(UserDevice $device): void
    {
        try {
            $device->loadMissing('user');

            $admins = User::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'super-admin', 'super_admin']);
                })
                ->get();

            if ($admins->isEmpty()) {
                return;
            }

            \Illuminate\Support\Facades\Notification::send(
                $admins,
                new \App\Notifications\NewUntrustedDeviceNotification($device)
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to notify admins of pending device', [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
