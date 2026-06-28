<?php

namespace App\Services\Simulator;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SimulatorSpecJsonParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $raw): array
    {
        $response = $this->normalizeEncoding($raw);
        $candidates = $this->buildCandidateStrings($response);

        $lastError = 'unknown';
        foreach ($candidates as $candidate) {
            $decoded = $this->tryDecode($candidate);
            if (is_array($decoded)) {
                return $decoded;
            }
            $lastError = json_last_error_msg();
        }

        Log::warning('SimulatorSpecJsonParser failed', [
            'json_error' => $lastError,
            'response_length' => strlen($response),
            'response_preview' => Str::limit($response, 800),
            'response_tail' => strlen($response) > 400 ? substr($response, -400) : null,
        ]);

        $hint = $this->detectFailureHint($response, $lastError);

        throw new \RuntimeException('فشل تحليل JSON من استجابة AI. '.$hint);
    }

    /**
     * @return list<string>
     */
    private function buildCandidateStrings(string $response): array
    {
        $candidates = [];
        $seen = [];

        $push = function (?string $value) use (&$candidates, &$seen): void {
            if ($value === null || $value === '') {
                return;
            }
            $value = trim($value);
            if ($value === '' || isset($seen[$value])) {
                return;
            }
            $seen[$value] = true;
            $candidates[] = $value;
        };

        $push($response);

        $stripped = $this->stripMarkdownFences($response);
        $push($stripped);

        $extracted = $this->extractBalancedJsonObject($stripped);
        $push($extracted);

        $jsonStart = strpos($stripped, '{');
        $jsonEnd = strrpos($stripped, '}');
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $push(substr($stripped, $jsonStart, $jsonEnd - $jsonStart + 1));
        }

        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/is', $response, $matches)) {
            $push($matches[1]);
        }

        return $candidates;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function tryDecode(string $json): ?array
    {
        foreach ($this->jsonRepairVariants($json) as $variant) {
            $decoded = json_decode($variant, true, 512, JSON_INVALID_UTF8_IGNORE);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (isset($decoded['meta'], $decoded['sections']) && is_array($decoded['sections'])) {
                    return $decoded;
                }
                if (isset($decoded['sections']) && is_array($decoded['sections'])) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function jsonRepairVariants(string $json): array
    {
        $variants = [trim($json)];
        $repaired = $this->repairCommonJsonIssues($json);
        if ($repaired !== $json) {
            $variants[] = $repaired;
        }

        return array_values(array_unique($variants));
    }

    private function repairCommonJsonIssues(string $json): string
    {
        $json = trim($json);
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json) ?? $json;
        $json = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], '"', $json);
        $json = preg_replace('/,\s*([}\]])/', '$1', $json) ?? $json;

        return $json;
    }

    private function stripMarkdownFences(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text) ?? $text;
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
        $text = preg_replace('/\s*```\s*$/i', '', $text) ?? $text;
        $text = preg_replace('/```(?:json)?/i', '', $text) ?? $text;

        return trim($text);
    }

    private function extractBalancedJsonObject(string $text): ?string
    {
        $startPos = strpos($text, '{');
        if ($startPos === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escapeNext = false;
        $length = strlen($text);

        for ($i = $startPos; $i < $length; $i++) {
            $char = $text[$i];

            if ($escapeNext) {
                $escapeNext = false;

                continue;
            }

            if ($char === '\\') {
                $escapeNext = true;

                continue;
            }

            if ($char === '"') {
                $inString = ! $inString;

                continue;
            }

            if ($inString) {
                continue;
            }

            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $startPos, $i - $startPos + 1);
                }
            }
        }

        return null;
    }

    private function normalizeEncoding(string $response): string
    {
        if (! mb_check_encoding($response, 'UTF-8')) {
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
        }

        return mb_convert_encoding($response, 'UTF-8', 'UTF-8');
    }

    private function detectFailureHint(string $response, string $lastError): string
    {
        $trimmed = rtrim($response);
        if ($trimmed !== '' && ! str_ends_with($trimmed, '}')) {
            return 'يبدو أن الردّ مقطوع (تجاوز حد التوكنات). جرّب موضوعاً أصغر أو زِد max_tokens للموديل.';
        }

        if (str_contains($response, '```')) {
            return 'الردّ محاط بـ markdown — تمت محاولة الاستخراج تلقائياً وفشلت.';
        }

        if ($lastError !== 'unknown' && $lastError !== 'No error') {
            return '('. $lastError .')';
        }

        return 'تأكد أن الموديل يُرجع JSON خام فقط.';
    }
}
