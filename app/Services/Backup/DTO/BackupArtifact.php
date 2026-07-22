<?php

namespace App\Services\Backup\DTO;

class BackupArtifact
{
    public function __construct(
        public readonly string $path,
        public readonly int $size,
        public readonly string $extension,
        public readonly ?string $mimeType = null,
        public readonly array $metadata = [],
    ) {}
}
