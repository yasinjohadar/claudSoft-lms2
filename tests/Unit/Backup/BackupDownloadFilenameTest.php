<?php

namespace Tests\Unit\Backup;

use App\Models\Backup;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BackupDownloadFilenameTest extends TestCase
{
    #[Test]
    public function database_gzip_downloads_as_sql_gz_not_gzip(): void
    {
        $backup = new Backup([
            'name' => 'النسخة اليومية_2026-07-22_06-00-47',
            'backup_type' => 'database',
            'compression_type' => 'gzip',
            'storage_path' => 'backups/20/dump.sql.gz',
            'metadata' => ['extension' => 'sql.gz'],
        ]);

        $this->assertSame('sql.gz', $backup->fileExtension());
        $this->assertSame(
            'النسخة اليومية_2026-07-22_06-00-47.sql.gz',
            $backup->downloadFilename()
        );
    }

    #[Test]
    public function zip_keeps_zip_extension(): void
    {
        $backup = new Backup([
            'name' => 'files-backup',
            'backup_type' => 'files',
            'compression_type' => 'zip',
            'storage_path' => 'backups/1/archive.zip',
        ]);

        $this->assertSame('zip', $backup->fileExtension());
        $this->assertSame('files-backup.zip', $backup->downloadFilename());
    }
}
