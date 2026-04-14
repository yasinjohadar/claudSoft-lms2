<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AssignmentAvailable
{
    use Dispatchable, SerializesModels;

    /**
     * @param Collection<int, int> $studentIds
     */
    public function __construct(
        public Assignment $assignment,
        public Collection $studentIds
    ) {}
}
