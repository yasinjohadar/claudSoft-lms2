<?php

namespace App\Services\Backup\Sources;

use App\Contracts\BackupSourceInterface;
use App\Models\Backup;
use App\Services\Backup\DTO\BackupArtifact;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DatabaseBackupSource implements BackupSourceInterface
{
    public function isAvailable(): bool
    {
        return $this->resolveBinary() !== null;
    }

    public function produce(Backup $backup, callable $onProgress, array $options = []): BackupArtifact
    {
        $compress = $options['compress'] ?? true;
        $binary = $this->resolveBinary();
        if ($binary === null) {
            throw new \RuntimeException('mysqldump / mariadb-dump is not available');
        }

        $tmpDir = storage_path('app/backups/tmp/' . $backup->id);
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $sqlPath = $tmpDir . '/dump.sql';
        $onProgress('dumping', 10, null, null);

        $connection = config('database.default');
        $db = config("database.connections.{$connection}");
        if (($db['driver'] ?? '') !== 'mysql' && ($db['driver'] ?? '') !== 'mariadb') {
            // Fall back to mysql connection name used elsewhere in the app
            $db = config('database.connections.mysql');
        }

        $host = $db['host'] ?? '127.0.0.1';
        $port = (string) ($db['port'] ?? 3306);
        $database = $db['database'] ?? '';
        $username = $db['username'] ?? '';
        $password = $db['password'] ?? '';

        $defaultsFile = $tmpDir . '/.my.cnf';
        $this->writeDefaultsFile($defaultsFile, $host, $port, $username, $password);

        $args = [
            $binary,
            '--defaults-extra-file=' . $defaultsFile,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--hex-blob',
            $database,
        ];

        $timeout = (int) config('backup.dump_timeout', 3600);
        $process = new Process($args);
        $process->setTimeout($timeout);
        // Critical: without this, Symfony buffers the entire dump in RAM (~hundreds of MB)
        // which on Windows often fails with errno=28 when C: is nearly full.
        $process->disableOutput();
        // mysqldump / MySQL client may write temp files to TMP/TEMP — keep them on the project disk.
        $process->setEnv([
            'TMP' => $tmpDir,
            'TEMP' => $tmpDir,
            'TMPDIR' => $tmpDir,
        ]);

        $freeBytes = @disk_free_space($tmpDir);
        if ($freeBytes !== false && $freeBytes < 512 * 1024 * 1024) {
            throw new \RuntimeException(
                'مساحة القرص غير كافية لملف النسخة المؤقت ('
                . round($freeBytes / (1024 * 1024))
                . ' MB متاحة). حرّر مساحة ثم أعد المحاولة.'
            );
        }

        $gzPath = $sqlPath . '.gz';
        $writeTarget = $compress ? $gzPath : $sqlPath;
        $out = $compress
            ? gzopen($writeTarget, 'wb' . (int) config('backup.gzip_level', 6))
            : fopen($writeTarget, 'wb');

        if ($out === false) {
            @unlink($defaultsFile);
            throw new \RuntimeException('Unable to open dump output file');
        }

        $bytesWritten = 0;
        $lastProgressAt = microtime(true);
        $stderr = '';
        $writeError = null;

        try {
            $process->run(function ($type, $buffer) use ($out, $compress, &$bytesWritten, &$lastProgressAt, $onProgress, &$stderr, &$writeError) {
                if ($type === Process::ERR) {
                    $stderr .= $buffer;
                    if (trim($buffer) !== '') {
                        Log::warning('mysqldump stderr: ' . trim($buffer));
                    }

                    return;
                }

                if ($type !== Process::OUT || $buffer === '' || $writeError !== null) {
                    return;
                }

                $written = $compress ? gzwrite($out, $buffer) : fwrite($out, $buffer);
                if ($written === false) {
                    $writeError = 'فشل كتابة ملف النسخة محلياً (تحقق من مساحة القرص، خاصة C:\\Temp على Windows).';

                    return;
                }
                $bytesWritten += $written;

                $now = microtime(true);
                if ($now - $lastProgressAt >= 2) {
                    $onProgress('dumping', min(49, 10 + (int) ($bytesWritten / (8 * 1024 * 1024))), $bytesWritten, null);
                    $lastProgressAt = $now;
                }
            });
        } finally {
            if ($compress) {
                gzclose($out);
            } else {
                fclose($out);
            }
            @unlink($defaultsFile);
        }

        if ($writeError !== null) {
            @unlink($writeTarget);
            throw new \RuntimeException($writeError);
        }

        if (!$process->isSuccessful()) {
            @unlink($writeTarget);
            $detail = trim($stderr) !== '' ? trim($stderr) : ('exit code ' . $process->getExitCode());
            throw new \RuntimeException('mysqldump failed: ' . $detail);
        }

        if (!file_exists($writeTarget) || filesize($writeTarget) === 0) {
            throw new \RuntimeException('Dump file is empty or missing');
        }

        $size = (int) filesize($writeTarget);
        $onProgress($compress ? 'compressing' : 'dumping', $compress ? 75 : 50, $size, $size);

        return new BackupArtifact(
            path: $writeTarget,
            size: $size,
            extension: $compress ? 'sql.gz' : 'sql',
            mimeType: $compress ? 'application/gzip' : 'application/sql',
            metadata: ['source' => 'mysqldump', 'binary' => basename($binary), 'stream_compressed' => $compress],
        );
    }

    public function resolveBinary(): ?string
    {
        $configured = config('backup.mysqldump_path');
        if (is_string($configured) && $configured !== '' && $this->isUsableBinary($configured)) {
            return $configured;
        }

        foreach (['mysqldump', 'mariadb-dump'] as $name) {
            $found = $this->which($name);
            if ($found !== null) {
                return $found;
            }
        }

        foreach ($this->candidateBinaryPaths() as $path) {
            if ($this->isUsableBinary($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function candidateBinaryPaths(): array
    {
        $paths = [
            'C:\\MAMP\\bin\\mysql\\bin\\mysqldump.exe',
            'C:\\MAMP\\bin\\mysql\\bin\\mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.4\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.11\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 11.4\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
        ];

        // Laragon / MAMP versioned folders
        foreach (glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe') ?: [] as $path) {
            $paths[] = $path;
        }
        foreach (glob('C:\\MAMP\\bin\\mysql\\*\\bin\\mysqldump.exe') ?: [] as $path) {
            $paths[] = $path;
        }

        return array_values(array_unique($paths));
    }

    protected function isUsableBinary(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        // Windows often reports .exe as not "executable" via is_executable()
        if (DIRECTORY_SEPARATOR === '\\') {
            return str_ends_with(strtolower($path), '.exe') || is_executable($path);
        }

        return is_executable($path);
    }

    protected function which(string $binary): ?string
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $cmd = $isWindows ? ['where', $binary] : ['which', $binary];
        $process = new Process($cmd);
        $process->setTimeout(5);
        $process->run();
        if (!$process->isSuccessful()) {
            return null;
        }
        $line = trim(explode("\n", str_replace("\r", '', $process->getOutput()))[0] ?? '');
        if ($line === '' || !$this->isUsableBinary($line)) {
            return null;
        }

        return $line;
    }

    protected function writeDefaultsFile(string $path, string $host, string $port, string $username, string $password): void
    {
        $content = "[client]\n"
            . "host=\"{$host}\"\n"
            . "port=\"{$port}\"\n"
            . "user=\"{$username}\"\n"
            . 'password="' . addcslashes($password, "\\\"") . "\"\n";

        file_put_contents($path, $content);
        @chmod($path, 0600);
    }

    protected function gzipFile(string $source, string $destination): void
    {
        $in = fopen($source, 'rb');
        $out = gzopen($destination, 'wb' . (int) config('backup.gzip_level', 6));
        if ($in === false || $out === false) {
            if (is_resource($in)) {
                fclose($in);
            }
            throw new \RuntimeException('Failed to open streams for gzip');
        }

        try {
            while (!feof($in)) {
                $chunk = fread($in, 1024 * 1024);
                if ($chunk === false) {
                    break;
                }
                gzwrite($out, $chunk);
            }
        } finally {
            fclose($in);
            gzclose($out);
        }

        if (!file_exists($destination) || filesize($destination) === 0) {
            throw new \RuntimeException('Gzip output is empty');
        }
    }
}
