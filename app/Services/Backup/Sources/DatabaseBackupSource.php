<?php

namespace App\Services\Backup\Sources;

use App\Contracts\BackupSourceInterface;
use App\Models\Backup;
use App\Services\Backup\DTO\BackupArtifact;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DatabaseBackupSource implements BackupSourceInterface
{
    /** أقل مساحة حرة مطلوبة قبل بدء الـ dump، لكل قرص معني. */
    private const MIN_FREE_BYTES = 512 * 1024 * 1024;

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
        // يمنع تجميع الـ dump كاملاً في ذاكرة PHP.
        // تنبيه: لا يمنع هذا تفريغ Symfony للمخرجات في ملف مؤقت على Windows —
        // فـ Process.php يبني WindowsPipes متى مُرِّر callback بغضّ النظر عن
        // disableOutput()، وتلك الملفات تُكتب في sys_get_temp_dir() لا في $tmpDir.
        // لذلك نفحص مساحة القرصين معاً أدناه. على Linux تُستخدم أنابيب حقيقية بلا ملفات.
        $process->disableOutput();
        // mysqldump / MySQL client may write temp files to TMP/TEMP — keep them on the project disk.
        // ملاحظة: هذا يؤثر على بيئة العملية الابنة فقط، لا على sys_get_temp_dir() في PHP نفسه.
        $process->setEnv([
            'TMP' => $tmpDir,
            'TEMP' => $tmpDir,
            'TMPDIR' => $tmpDir,
        ]);

        foreach ($this->diskSpaceTargets($tmpDir) as $label => $dir) {
            $freeBytes = @disk_free_space($dir);
            if ($freeBytes !== false && $freeBytes < self::MIN_FREE_BYTES) {
                throw new \RuntimeException(sprintf(
                    'مساحة القرص غير كافية في %s (%s): %d MB متاحة والمطلوب %d MB على الأقل. حرّر مساحة ثم أعد المحاولة.',
                    $label,
                    $dir,
                    (int) round($freeBytes / (1024 * 1024)),
                    (int) round(self::MIN_FREE_BYTES / (1024 * 1024))
                ));
            }
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
            $exitCode = $process->getExitCode();
            $detail = trim($stderr) !== '' ? trim($stderr) : ('exit code ' . $exitCode);

            // 5 = EX_EOF في mysqldump، أي فشل الكتابة إلى المخرجات. السبب الأشيع
            // امتلاء القرص الذي تُكتب عليه المخرجات (أو ملف Symfony المؤقت على Windows)،
            // و«exit code 5» وحده لا يقول ذلك للأدمن.
            if ($exitCode === 5) {
                $detail .= ' — فشل في كتابة مخرجات mysqldump، غالباً لامتلاء القرص. '
                    . $this->diskSpaceSummary($tmpDir);
            }

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

    /**
     * الأقراص التي يجب أن تتوفر فيها مساحة قبل بدء الـ dump.
     *
     * @return array<string, string> label => directory
     */
    private function diskSpaceTargets(string $tmpDir): array
    {
        $targets = ['مجلد النسخ المؤقت' => $tmpDir];

        // على Windows تُفرَّغ مخرجات العملية في ملف داخل sys_get_temp_dir()،
        // وهو غالباً على قرص آخر (C:) غير قرص المشروع.
        $systemTmp = sys_get_temp_dir();
        if (\DIRECTORY_SEPARATOR === '\\' && $systemTmp !== '' && ! $this->sameVolume($systemTmp, $tmpDir)) {
            $targets['مجلد temp النظام'] = $systemTmp;
        }

        return $targets;
    }

    private function sameVolume(string $a, string $b): bool
    {
        return strtoupper(substr($a, 0, 2)) === strtoupper(substr($b, 0, 2));
    }

    /**
     * ملخّص المساحة الحرة لكل قرص معني — يُضاف إلى رسالة الخطأ.
     */
    private function diskSpaceSummary(string $tmpDir): string
    {
        $parts = [];

        foreach ($this->diskSpaceTargets($tmpDir) as $label => $dir) {
            $free = @disk_free_space($dir);
            $parts[] = $free === false
                ? sprintf('%s (%s): المساحة غير معروفة', $label, $dir)
                : sprintf('%s (%s): %d MB حرة', $label, $dir, (int) round($free / (1024 * 1024)));
        }

        return implode(' — ', $parts);
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
