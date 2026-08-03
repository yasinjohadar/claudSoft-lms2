<?php

namespace App\Services\Ai;

use App\Exceptions\Ai\AiProviderException;
use Throwable;

/**
 * Turns raw provider error text into an actionable kind.
 *
 * Legacy providers return an empty string and stash the reason in getLastError(),
 * so the message is all we have to work with.
 */
class AiErrorClassifier
{
    public function classify(?string $message): string
    {
        $text = mb_strtolower(trim((string) $message));
        if ($text === '') {
            return AiProviderException::KIND_EMPTY;
        }

        // Credit / key problems: retrying only wastes the remaining sections.
        if ($this->matches($text, [
            'invalid api key', 'incorrect api key', 'unauthorized', 'unauthenticated',
            'insufficient', 'credit', 'quota exceeded', 'billing', 'payment required',
            'access denied', 'forbidden', 'api key',
        ]) || $this->hasStatus($text, [401, 402, 403])) {
            return AiProviderException::KIND_AUTH;
        }

        // Request rejected for size before any generation happened.
        if ($this->matches($text, [
            'too large', 'request too large', 'context length', 'maximum context',
            'reduce the length', 'max_tokens', 'string too long', 'prompt is too long',
        ]) || $this->hasStatus($text, [413])) {
            return AiProviderException::KIND_TOO_LARGE;
        }

        if ($this->matches($text, [
            'rate limit', 'rate_limit', 'too many requests', 'tpm', 'rpm',
            'try again in', 'please slow down', 'overloaded', 'capacity',
        ]) || $this->hasStatus($text, [429])) {
            return AiProviderException::KIND_RATE_LIMIT;
        }

        return AiProviderException::KIND_TRANSIENT;
    }

    /**
     * Providers usually spell out "try again in 8.5s" — honour it instead of guessing.
     */
    public function retryAfterSeconds(?string $message): ?int
    {
        $text = mb_strtolower(trim((string) $message));
        if ($text === '') {
            return null;
        }

        if (preg_match('/(?:try again in|retry after|retry-after[:\s])\s*([0-9]+(?:\.[0-9]+)?)\s*(ms|s|sec|seconds|m|min|minutes)?/i', $text, $m)) {
            $value = (float) $m[1];
            $unit = strtolower($m[2] ?? 's');
            $seconds = match (true) {
                $unit === 'ms' => $value / 1000,
                in_array($unit, ['m', 'min', 'minutes'], true) => $value * 60,
                default => $value,
            };

            return max(1, min(120, (int) ceil($seconds)));
        }

        return null;
    }

    public function toException(?string $message, ?Throwable $previous = null): AiProviderException
    {
        $text = trim((string) $message);
        $kind = $this->classify($text);

        return new AiProviderException(
            $text !== '' ? $text : 'لم يتم استلام استجابة من موديل AI.',
            $kind,
            $this->retryAfterSeconds($text),
            $previous,
        );
    }

    public function fromThrowable(Throwable $e): AiProviderException
    {
        if ($e instanceof AiProviderException) {
            return $e;
        }

        $message = $e->getMessage();
        if ($e instanceof \Illuminate\Http\Client\ConnectionException || str_contains(mb_strtolower($message), 'timeout')) {
            return new AiProviderException($message, AiProviderException::KIND_TRANSIENT, null, $e);
        }

        return $this->toException($message, $e);
    }

    /**
     * @param  list<string>  $needles
     */
    private function matches(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<int>  $codes
     */
    private function hasStatus(string $text, array $codes): bool
    {
        foreach ($codes as $code) {
            if (preg_match('/\b'.$code.'\b/', $text)) {
                return true;
            }
        }

        return false;
    }
}
