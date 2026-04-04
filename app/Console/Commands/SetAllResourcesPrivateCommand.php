<?php

namespace App\Console\Commands;

use App\Models\Resource;
use Illuminate\Console\Command;

class SetAllResourcesPrivateCommand extends Command
{
    protected $signature = 'resources:set-all-private
                            {--force : تنفيذ مباشرة بدون سؤال تأكيد}';

    protected $description = 'تعيين نطاق المورد (resource_scope) إلى «خاص» لجميع الموارد غير المحذوفة دفعة واحدة';

    public function handle(): int
    {
        $query = Resource::query();
        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('لا توجد موارد في قاعدة البيانات.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            if (! $this->confirm(
                "سيتم تحديث {$total} موردًا إلى النطاق «خاص». لن تظهر في مكتبة الموارد العامة للطالب. المتابعة؟",
                false
            )) {
                $this->info('تم الإلغاء.');

                return self::SUCCESS;
            }
        }

        $updated = $query->update([
            'resource_scope' => Resource::SCOPE_PRIVATE,
        ]);

        $this->info("تم تحديث {$updated} موردًا إلى النطاق «خاص».");

        return self::SUCCESS;
    }
}
