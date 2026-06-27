<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleServiceAccountTokenService
{
    public function getAccessToken(array $credentials, array $scopes): string
    {
        $email = $credentials['client_email'] ?? '';
        $cacheKey = 'google_sa_token_' . md5($email . implode('|', $scopes));

        return Cache::remember($cacheKey, 3300, function () use ($credentials, $scopes) {
            $now = time();
            $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim = $this->base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => implode(' ', $scopes),
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $unsigned = "{$header}.{$claim}";
            $privateKey = openssl_pkey_get_private($credentials['private_key']);

            if (! $privateKey) {
                throw new \RuntimeException('مفتاح Service Account غير صالح');
            }

            openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = $unsigned . '.' . $this->base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('فشل الحصول على Access Token: ' . $response->body());
            }

            $token = $response->json('access_token');

            if (! $token) {
                throw new \RuntimeException('Access Token فارغ من Google');
            }

            return $token;
        });
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
