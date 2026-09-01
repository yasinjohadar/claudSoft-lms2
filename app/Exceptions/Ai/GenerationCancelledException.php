<?php

namespace App\Exceptions\Ai;

use RuntimeException;

/**
 * The admin asked to stop a running generation. Raised at the next safe
 * checkpoint (before starting a new attempt), never mid-request.
 */
class GenerationCancelledException extends RuntimeException
{
    public function __construct(string $message = 'تم إيقاف التوليد بطلب من المستخدم.')
    {
        parent::__construct($message);
    }
}
