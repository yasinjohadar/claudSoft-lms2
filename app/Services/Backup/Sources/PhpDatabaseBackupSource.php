<?php

namespace App\Services\Backup\Sources;

use App\Contracts\BackupSourceInterface;
use App\Models\Backup;
use App\Services\Backup\DTO\BackupArtifact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Legacy chunked PHP dump — fallback only when mysqldump is unavailable.
 */
class PhpDatabaseBackupSource implements BackupSourceInterface
{
    public function isAvailable(): bool
    {
        return (bool) config('backup.php_fallback_enabled', true);
    }

    public function produce(Backup $backup, callable $onProgress, array $options = []): BackupArtifact
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('PHP database dump fallback is disabled');
        }

        $compress = $options['compress'] ?? true;
        $maxBytes = (int) config('backup.php_fallback_max_bytes', 512 * 1024 * 1024);

        $tmpDir = storage_path('app/backups/tmp/' . $backup->id);
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $sqlPath = $tmpDir . '/dump.sql';
        $onProgress('dumping', 5, 0, null);

        Log::warning('Using PHP database dump fallback — prefer mysqldump for large databases', [
            'backup_id' => $backup->id,
        ]);

        $database = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $tablesKey = 'Tables_in_' . $database;

        $handle = fopen($sqlPath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open dump file for writing');
        }

        $bytesWritten = 0;
        $lastProgressAt = microtime(true);
        $write = function (string $chunk) use ($handle, &$bytesWritten, $maxBytes) {
            $len = fwrite($handle, $chunk);
            if ($len === false) {
                throw new \RuntimeException('Failed writing dump file');
            }
            $bytesWritten += $len;
            if ($bytesWritten > $maxBytes) {
                throw new \RuntimeException(
                    "PHP dump exceeded size limit ({$maxBytes} bytes). Install mysqldump or raise backup.php_fallback_max_bytes."
                );
            }
        };

        try {
            $write("-- Database Backup (PHP fallback)\n");
            $write('-- Generated: ' . now()->toDateTimeString() . "\n");
            $write("-- Database: {$database}\n\n");
            $write("SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tableCount = max(count($tables), 1);
            $index = 0;

            foreach ($tables as $table) {
                $tableName = $table->$tablesKey;
                $index++;
                $baseProgress = 5 + (int) ((($index - 1) / $tableCount) * 45);
                $onProgress('dumping', $baseProgress, $bytesWritten, null);

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $write("DROP TABLE IF EXISTS `{$tableName}`;\n");
                $write($createTable[0]->{'Create Table'} . ";\n\n");

                $chunkIndex = 0;
                DB::table($tableName)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use (
                    $write,
                    $tableName,
                    &$bytesWritten,
                    &$lastProgressAt,
                    $onProgress,
                    $baseProgress,
                    &$chunkIndex
                ) {
                    if ($rows->isEmpty()) {
                        return;
                    }
                    $chunkIndex++;
                    $firstRow = (array) $rows->first();
                    $columns = array_map(fn ($col) => "`{$col}`", array_keys($firstRow));
                    $columnsStr = implode(', ', $columns);
                    $values = [];
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $valArray = array_map(function ($val) {
                            if ($val === null) {
                                return 'NULL';
                            }

                            return DB::getPdo()->quote((string) $val);
                        }, array_values($rowArray));
                        $values[] = '(' . implode(', ', $valArray) . ')';
                    }
                    $write("INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n" . implode(",\n", $values) . ";\n\n");

                    $now = microtime(true);
                    if ($now - $lastProgressAt >= 2 || $chunkIndex % 5 === 0) {
                        $intra = min(4, (int) floor($chunkIndex / 10));
                        $onProgress('dumping', min(49, $baseProgress + $intra), $bytesWritten, null);
                        $lastProgressAt = $now;
                    }
                });
            }

            $write("SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }

        if (!file_exists($sqlPath) || filesize($sqlPath) === 0) {
            throw new \RuntimeException('PHP dump file is empty');
        }

        $onProgress('dumping', 50, filesize($sqlPath), filesize($sqlPath));

        if (!$compress) {
            return new BackupArtifact(
                path: $sqlPath,
                size: (int) filesize($sqlPath),
                extension: 'sql',
                mimeType: 'application/sql',
                metadata: ['source' => 'php_fallback'],
            );
        }

        $onProgress('compressing', 60, null, null);
        $gzPath = $sqlPath . '.gz';
        $in = fopen($sqlPath, 'rb');
        $out = gzopen($gzPath, 'wb6');
        if ($in === false || $out === false) {
            throw new \RuntimeException('Failed to gzip PHP dump');
        }
        while (!feof($in)) {
            $chunk = fread($in, 1024 * 1024);
            if ($chunk !== false && $chunk !== '') {
                gzwrite($out, $chunk);
            }
        }
        fclose($in);
        gzclose($out);
        @unlink($sqlPath);

        $size = (int) filesize($gzPath);
        $onProgress('compressing', 75, $size, $size);

        return new BackupArtifact(
            path: $gzPath,
            size: $size,
            extension: 'sql.gz',
            mimeType: 'application/gzip',
            metadata: ['source' => 'php_fallback'],
        );
    }
}
