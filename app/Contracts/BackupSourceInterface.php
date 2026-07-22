<?php

namespace App\Contracts;

use App\Models\Backup;
use App\Services\Backup\DTO\BackupArtifact;

interface BackupSourceInterface
{
    /**
     * Produce a local backup artifact.
     *
     * @param  callable(string $stage, int $progress, ?int $bytesProcessed, ?int $bytesTotal): void  $onProgress
     */
    public function produce(Backup $backup, callable $onProgress, array $options = []): BackupArtifact;

    /**
     * Whether this source can run in the current environment.
     */
    public function isAvailable(): bool;
}
