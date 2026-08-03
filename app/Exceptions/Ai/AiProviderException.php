<?php

namespace App\Exceptions\Ai;

use RuntimeException;
use Throwable;

/**
 * Provider failure carrying enough classification for the staged documentation
 * generator to decide between backing off, shrinking the request, or aborting.
 */
class AiProviderException extends RuntimeException
{
    public const KIND_RATE_LIMIT = 'rate_limit';

    public const KIND_TOO_LARGE = 'too_large';

    public const KIND_AUTH = 'auth';

    public const KIND_TRANSIENT = 'transient';

    public const KIND_EMPTY = 'empty';

    public function __construct(
        string $message,
        public readonly string $kind = self::KIND_TRANSIENT,
        public readonly ?int $retryAfterSeconds = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /** Nothing the pipeline can do about it — stop burning API calls. */
    public function isFatal(): bool
    {
        return $this->kind === self::KIND_AUTH;
    }

    /** The request itself was rejected as too big; retry with fewer tokens. */
    public function needsSmallerRequest(): bool
    {
        return $this->kind === self::KIND_TOO_LARGE;
    }
}
