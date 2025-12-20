<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $source = 'wpforms'): Response
    {
        // Try to get webhook secret from database first
        $webhookToken = \App\Models\WebhookToken::getActiveToken($source);
        $secret = $webhookToken?->token;

        // Fallback to config if no database token found
        if (!$secret) {
            $secret = config("webhooks.{$source}.incoming.secret")
                   ?? config("webhooks.{$source}.secret");
        }

        // Get allowed IPs from database or config
        $allowedIps = $webhookToken?->allowed_ips 
                   ?? config("webhooks.{$source}.allowed_ips", []);

        // If no secret is configured, allow the request (for development)
        if (!$secret) {
            return $next($request);
        }

        // For WPForms: Check signature or API key
        if ($source === 'wpforms') {
            return $this->verifyWPForms($request, $secret, $next, $allowedIps);
        }

        // For n8n: Check HMAC signature
        if ($source === 'n8n') {
            return $this->verifyN8n($request, $secret, $next);
        }

        // Default: require API key in header
        return $this->verifyApiKey($request, $secret, $next);
    }

    /**
     * Verify WPForms webhook
     */
    private function verifyWPForms(Request $request, string $secret, Closure $next, array $allowedIps = []): Response
    {
        // Method 1: Check for API key in header
        $apiKey = $request->header('X-WPForms-API-Key')
                  ?? $request->header('X-API-Key')
                  ?? $request->input('api_key');

        if ($apiKey && hash_equals($secret, $apiKey)) {
            return $next($request);
        }

        // Method 2: Check signature (if WPForms supports it)
        $signature = $request->header('X-WPForms-Signature');
        if ($signature) {
            $payload = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $payload, $secret);

            if (hash_equals($expectedSignature, $signature)) {
                return $next($request);
            }
        }

        // Method 3: IP whitelist (optional)
        // $allowedIps is already set in handle() method
        if (!empty($allowedIps) && \in_array($request->ip(), $allowedIps, true)) {
            return $next($request);
        }

        // If no verification method passed, reject
        if ($apiKey || $signature) {
            return response()->json([
                'error' => 'Invalid webhook signature or API key',
            ], 401);
        }

        // For development: allow if no authentication provided and in local environment
        if (app()->environment('local')) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Webhook authentication required',
        ], 401);
    }

    /**
     * Verify n8n webhook using HMAC signature
     */
    private function verifyN8n(Request $request, string $secret, Closure $next): Response
    {
        // Check for signature in header
        $signature = $request->header('X-N8N-Signature')
                  ?? $request->header('X-Webhook-Signature');

        $timestamp = $request->header('X-Webhook-Timestamp');

        if ($signature && $timestamp) {
            $payload = $request->getContent();
            $data = $payload . $timestamp;
            $expectedSignature = hash_hmac('sha256', $data, $secret);

            if (hash_equals($expectedSignature, $signature)) {
                return $next($request);
            }
        }

        // Fallback to API key method
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');

        if ($apiKey && hash_equals($secret, $apiKey)) {
            return $next($request);
        }

        // For development: allow if no authentication provided and in local environment
        if (app()->environment('local') && !$signature && !$apiKey) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Invalid webhook signature or API key',
        ], 401);
    }

    /**
     * Verify using simple API key
     */
    private function verifyApiKey(Request $request, string $secret, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');

        if ($apiKey && hash_equals($secret, $apiKey)) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Invalid API key',
        ], 401);
    }
}
