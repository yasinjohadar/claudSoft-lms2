<?php

namespace App\Exceptions\Ai;

use RuntimeException;

/**
 * Some sections could not be produced, but everything finished is safely stored.
 * The pipeline turns this into a paused generation instead of a failure.
 *
 * @param  list<string>  $failedHeadings
 */
class ResumableIncompleteException extends RuntimeException
{
    /**
     * @param  list<string>  $failedHeadings
     */
    public function __construct(
        string $message,
        public readonly int $done = 0,
        public readonly int $planned = 0,
        public readonly array $failedHeadings = [],
    ) {
        parent::__construct($message);
    }
}
