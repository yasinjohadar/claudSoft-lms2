<?php

namespace App\Console\Commands;

use App\Models\StudentGift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixGiftImagePathsCommand extends Command
{
    protected $signature = 'gifts:fix-image-paths
                            {--dry-run : عرض التغييرات دون تنفيذها}';

    protected $description = 'إصلاح مسارات صور الهدايا التي أُنشئت كمجلدات .webp بدلاً من ملفات';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $publicRoot = storage_path('app/public');
        $fixed = 0;
        $skipped = 0;

        StudentGift::query()
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($gifts) use ($publicRoot, $dryRun, &$fixed, &$skipped) {
                foreach ($gifts as $gift) {
                    $relativePath = ltrim($gift->image_path, '/');
                    $absolutePath = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

                    if (is_file($absolutePath)) {
                        $skipped++;

                        continue;
                    }

                    if (! is_dir($absolutePath)) {
                        $this->warn("Gift #{$gift->id}: path missing on disk — {$relativePath}");
                        $skipped++;

                        continue;
                    }

                    $innerFiles = File::files($absolutePath);

                    if (count($innerFiles) !== 1) {
                        $this->warn("Gift #{$gift->id}: expected one file inside {$relativePath}, found ".count($innerFiles));
                        $skipped++;

                        continue;
                    }

                    $innerFile = $innerFiles[0];
                    $parentDir = dirname($relativePath);
                    $newRelativePath = ($parentDir !== '.' ? $parentDir.'/' : '').basename($innerFile->getPathname());
                    $newAbsolutePath = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $newRelativePath);

                    if ($dryRun) {
                        $this->line("[dry-run] Gift #{$gift->id}: would move to {$newRelativePath}");
                        $fixed++;

                        continue;
                    }

                    if (is_file($newAbsolutePath)) {
                        File::delete($newAbsolutePath);
                    }

                    File::ensureDirectoryExists(dirname($newAbsolutePath));

                    if (! rename($innerFile->getPathname(), $newAbsolutePath)) {
                        $this->error("Gift #{$gift->id}: failed to move file to {$newRelativePath}");
                        $skipped++;

                        continue;
                    }

                    File::deleteDirectory($absolutePath);
                    $gift->update(['image_path' => $newRelativePath]);
                    $this->info("Gift #{$gift->id}: fixed → {$newRelativePath}");
                    $fixed++;
                }
            });

        $this->newLine();
        $this->info("Done. Fixed: {$fixed}, skipped: {$skipped}".($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
