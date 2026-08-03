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

        $candidates = $this->jsonCandidateStrings($response);

        foreach ($candidates as $jsonString) {
            $decoded = json_decode($jsonString, true, 512, JSON_INVALID_UTF8_IGNORE);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            $repaired = $this->attemptRepairTruncatedJson($jsonString);
            if ($repaired !== null) {
                $decoded = json_decode($repaired, true, 512, JSON_INVALID_UTF8_IGNORE);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    Log::info('Recovered truncated JSON in parseJSONResponse');

                    return $decoded;
                }
            }
        }

        $heuristic = $this->extractWizardFieldsHeuristically($response);
        if ($heuristic !== []) {
            Log::info('Recovered wizard fields heuristically after JSON parse failure');

            return $heuristic;
        }

        if ($candidates !== []) {
            Log::warning('JSON decode error in parseJSONResponse', [
                'error' => json_last_error_msg(),
                'error_code' => json_last_error(),
                'json_preview' => mb_substr($candidates[0], 0, 200),
            ]);
        }

        return [];
    }

    /**
     * @return list<string>
     */
    protected function jsonCandidateStrings(string $response): array
    {
        $trimmed = trim($response);
        if (preg_match('/^```(?:json)?\s*/i', $trimmed)) {
            $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
            $trimmed = trim($trimmed);
        }

        $candidates = [];
        if ($trimmed !== '' && str_starts_with($trimmed, '{')) {
            $candidates[] = $trimmed;
        }

        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $slice = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            if (! in_array($slice, $candidates, true)) {
                $candidates[] = $slice;
            }
        }

        // Truncated responses often have opening `{` but no closing `}`
        if ($jsonStart !== false && ($jsonEnd === false || $jsonEnd < $jsonStart)) {
            $openEnded = substr($response, $jsonStart);
            if (! in_array($openEnded, $candidates, true)) {
                $candidates[] = $openEnded;
            }
        }

        return $candidates;
    }

    protected function attemptRepairTruncatedJson(string $json): ?string
    {
        $json = trim($json);
        if ($json === '' || ! str_starts_with($json, '{')) {
            return null;
        }

        // Close an open string if quotes are unbalanced.
        $quoteCount = preg_match_all('/(?<!\\\\)"/', $json) ?: 0;
        if ($quoteCount % 2 === 1) {
            $json .= '"';
        }

        $opens = substr_count($json, '{') - substr_count($json, '}');
        $openArrays = substr_count($json, '[') - substr_count($json, ']');
        if ($opens < 0 && $openArrays < 0) {
            return null;
        }

        // Trailing comma before close
        $json = rtrim($json);
        $json = preg_replace('/,\s*$/', '', $json) ?? $json;

        if ($openArrays > 0) {
            $json .= str_repeat(']', $openArrays);
        }
        if ($opens > 0) {
            $json .= str_repeat('}', $opens);
        }

        return $json;
    }

    /**
     * Last-resort extraction when JSON is too broken to decode.
     *
     * @return array<string, mixed>
     */
    protected function extractWizardFieldsHeuristically(string $response): array
    {
        $out = [];

        if (preg_match('/"title"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/u', $response, $m)) {
            $title = stripcslashes($m[1]);
            if (trim($title) !== '') {
                $out['title'] = trim($title);
            }
        }

        if (preg_match('/"slug"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/u', $response, $m)) {
            $slug = stripcslashes($m[1]);
            if (trim($slug) !== '') {
                $out['slug'] = trim($slug);
            }
        }

        if (preg_match('/"excerpt"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/u', $response, $m)) {
            $excerpt = stripcslashes($m[1]);
            if (trim($excerpt) !== '') {
                $out['excerpt'] = trim($excerpt);
            }
        }

        // Prefer HTML blocks even if the JSON string was cut mid-content.
        if (preg_match('/<(section|article|div|h[1-6]|main)\b[\s\S]{20,}/i', $response, $m)) {
            $html = trim($m[0]);
            // Trim trailing broken JSON fragments after last plausible tag close
            if (preg_match('/^(.*<\/(?:section|article|div|p|ul|ol|table|pre|h[1-6])>)/is', $html, $closed)) {
                $html = trim($closed[1]);
            }
            if ($html !== '') {
                $out['content'] = $html;
            }
        } elseif (preg_match('/"content"\s*:\s*"((?:\\\\.|[^"\\\\])*)/u', $response, $m)) {
            $content = stripcslashes($m[1]);
            if (trim($content) !== '') {
                $out['content'] = trim($content);
            }
        }

        if (empty($out['title']) && empty($out['content']) && empty($out['html'])) {
            return [];
        }

        return $out;
    }
}
