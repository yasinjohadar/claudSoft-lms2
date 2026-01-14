<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeoLocationService
{
    /**
     * Get location information from IP address.
     * Uses ip-api.com free API (45 requests per minute).
     * 
     * @param string $ipAddress
     * @return array|null Returns array with country, city, region, etc. or null on failure
     */
    public function getLocationFromIp(string $ipAddress): ?array
    {
        // Skip local/private IPs
        if ($this->isLocalIp($ipAddress)) {
            return [
                'country' => null,
                'country_name' => 'Local',
                'city' => 'Local',
                'region' => null,
                'timezone' => null,
                'isp' => null,
            ];
        }

        // Check cache first (cache for 24 hours)
        $cacheKey = "geo_location_{$ipAddress}";
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Use ip-api.com free API (supports both http and https)
            // Format: http://ip-api.com/json/{ip}?fields=status,message,country,countryCode,city,region,regionName,timezone,isp
            $url = "http://ip-api.com/json/{$ipAddress}";
            $response = Http::timeout(5)
                ->withoutVerifying() // Allow self-signed certificates if needed
                ->get($url, [
                    'fields' => 'status,message,country,countryCode,city,region,regionName,timezone,isp,lat,lon'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $location = [
                        'country' => $data['countryCode'] ?? null,
                        'country_name' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? $data['region'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'isp' => $data['isp'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                    ];

                    // Cache for 24 hours
                    Cache::put($cacheKey, $location, now()->addHours(24));
                    
                    return $location;
                } else {
                    Log::warning('GeoLocation API returned error', [
                        'ip' => $ipAddress,
                        'message' => $data['message'] ?? 'Unknown error',
                        'response' => $data,
                    ]);
                }
            } else {
                Log::warning('GeoLocation API request failed', [
                    'ip' => $ipAddress,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get geolocation', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Return null on failure (don't cache failures)
        return null;
    }

    /**
     * Check if IP is local/private.
     */
    protected function isLocalIp(string $ip): bool
    {
        // IPv4 local ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        // IPv6 local ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Check for localhost
            if ($ip === '::1' || $ip === '0:0:0:0:0:0:0:1') {
                return true;
            }
            // Check for private ranges (simplified)
            if (strpos($ip, 'fc00:') === 0 || strpos($ip, 'fe80:') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get formatted location string (Country, City).
     */
    public function getFormattedLocation(?array $location): string
    {
        if (!$location) {
            return '-';
        }

        $parts = [];
        
        if (!empty($location['city'])) {
            $parts[] = $location['city'];
        }
        
        if (!empty($location['country_name'])) {
            $parts[] = $location['country_name'];
        } elseif (!empty($location['country'])) {
            $parts[] = $location['country'];
        }

        return !empty($parts) ? implode(', ', $parts) : '-';
    }
}
