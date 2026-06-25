<?php

namespace App\Console\Commands;

use App\Services\CodeExecution\PistonClient;
use Illuminate\Console\Command;

class PistonHealthCommand extends Command
{
    protected $signature = 'challenges:piston-health';

    protected $description = 'اختبار اتصال Piston وعرض اللغات المتاحة';

    public function handle(PistonClient $client): int
    {
        $url = config('challenges.piston.url');

        $this->info("PISTON_URL: {$url}");

        if (! $client->isConfigured()) {
            $this->error('PISTON_URL غير مُعد في ملف البيئة.');

            return Command::FAILURE;
        }

        if (! $client->ping()) {
            $this->error('فشل الاتصال بمحرك Piston. تأكد من تشغيل الخدمة.');

            return Command::FAILURE;
        }

        $this->info('✓ الاتصال بـ Piston ناجح');

        $runtimes = $client->runtimes();

        if (empty($runtimes)) {
            $this->warn('لم يتم العثور على لغات تنفيذ.');

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('اللغات المتاحة:');

        $rows = collect($runtimes)->map(fn ($rt) => [
            $rt['language'] ?? '-',
            $rt['version'] ?? '-',
            implode(', ', $rt['aliases'] ?? []),
        ])->all();

        $this->table(['اللغة', 'الإصدار', 'الأسماء البديلة'], $rows);

        return Command::SUCCESS;
    }
}
