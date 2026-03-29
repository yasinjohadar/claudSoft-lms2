<?php

namespace App\Services\Ai\Concerns;

use Illuminate\Support\Facades\Log;

trait ParsesAiJsonResponse
{
    /**
     * @return array<string, mixed>
     */
    protected function parseJSONResponse(string $response): array
    {
        if (! mb_check_encoding($response, 'UTF-8')) {
            Log::warning('Invalid UTF-8 encoding detected in response, attempting to fix');
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
            if (! mb_check_encoding($response, 'UTF-8')) {
                $response = mb_convert_encoding($response, 'UTF-8', ['UTF-8', 'ISO-8859-1', 'Windows-1256']);
            }
        }

        $response = mb_convert_encoding($response, 'UTF-8', 'UTF-8');

        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);

            try {
                $decoded = json_decode($jsonString, true, 512, JSON_INVALID_UTF8_IGNORE);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }

                Log::warning('JSON decode error in parseJSONResponse', [
                    'error' => json_last_error_msg(),
                    'error_code' => json_last_error(),
                    'json_preview' => mb_substr($jsonString, 0, 200),
                ]);
            } catch (\JsonException $e) {
                Log::error('JSON exception in parseJSONResponse: '.$e->getMessage(), [
                    'json_preview' => mb_substr($jsonString, 0, 200),
                ]);
            }
        }

        return [];
    }
}
