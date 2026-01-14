<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceTrackingService
{
    protected GeoLocationService $geoLocationService;

    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }
    /**
     * Track or update device information when user logs in.
     */
    public function trackDeviceOnLogin(User $user, Request $request): UserDevice
    {
        $fingerprint = $this->generateDeviceFingerprint($request);
        $deviceInfo = $this->detectDeviceInfo($request);
        $ipAddress = $request->ip();

        // Check if device already exists for this user
        $device = UserDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        // Get geolocation
        $location = $this->geoLocationService->getLocationFromIp($ipAddress);
        
        if ($device) {
            // Update existing device
            $device->incrementLogins();
            $device->updateLastUsed($ipAddress);
            
            // Prepare meta data
            $meta = $device->meta ?? [];
            if ($location) {
                $meta['location'] = $location;
            }
            
            // Update device info if changed
            $device->update([
                'browser' => $deviceInfo['browser'],
                'browser_version' => $deviceInfo['browser_version'],
                'platform' => $deviceInfo['platform'],
                'platform_version' => $deviceInfo['platform_version'],
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'meta' => $meta,
            ]);
        } else {
            // Prepare meta data
            $meta = [];
            if ($location) {
                $meta['location'] = $location;
            }
            
            // Create new device
            $device = UserDevice::create([
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
                'device_name' => null, // User can set this later
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
                'is_trusted' => false,
                'is_blocked' => false,
                'meta' => $meta,
            ]);
        }

        return $device;
    }

    /**
     * Generate a unique device fingerprint.
     */
    public function generateDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
            $request->ip(),
        ];

        $fingerprintString = implode('|', array_filter($components));
        
        return hash('sha256', $fingerprintString);
    }

    /**
     * Detect device information from request.
     */
    public function detectDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';
        
        // Detect device type
        $deviceType = $this->detectDeviceType($userAgent);
        
        // Detect browser
        $browser = $this->detectBrowser($userAgent);
        $browserVersion = $this->detectBrowserVersion($userAgent, $browser);
        
        // Detect platform
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

    /**
     * Detect device type from user agent.
     */
    protected function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop/i', $userAgent)) {
            // Check if it's a tablet
            if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }

    /**
     * Detect browser from user agent.
     */
    protected function detectBrowser(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        if (preg_match('/edg/i', $userAgent)) {
            return 'Edge';
        } elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
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

    /**
     * Detect browser version from user agent.
     */
    protected function detectBrowserVersion(string $userAgent, string $browser): ?string
    {
        $pattern = '';
        
        switch (strtolower($browser)) {
            case 'chrome':
                $pattern = '/chrome\/([0-9.]+)/i';
                break;
            case 'firefox':
                $pattern = '/firefox\/([0-9.]+)/i';
                break;
            case 'safari':
                $pattern = '/version\/([0-9.]+)/i';
                break;
            case 'edge':
                $pattern = '/edg\/([0-9.]+)/i';
                break;
            case 'opera':
                $pattern = '/(?:opera|opr)\/([0-9.]+)/i';
                break;
        }
        
        if ($pattern && preg_match($pattern, $userAgent, $matches)) {
            return $matches[1] ?? null;
        }
        
        return null;
    }

    /**
     * Detect platform from user agent.
     */
    protected function detectPlatform(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        if (preg_match('/windows/i', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return 'iOS';
        }
        
        return 'Unknown';
    }

    /**
     * Detect platform version from user agent.
     */
    protected function detectPlatformVersion(string $userAgent, string $platform): ?string
    {
        $pattern = '';
        
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

    /**
     * Check if device is blocked.
     */
    public function isDeviceBlocked(User $user, Request $request): bool
    {
        $fingerprint = $this->generateDeviceFingerprint($request);
        
        $device = UserDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->first();
        
        return $device && $device->is_blocked;
    }
}
