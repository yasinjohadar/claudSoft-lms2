<?php

namespace App\Models\Concerns;

use App\Services\Admin\ActivityLogService;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    /**
     * @return list<string>
     */
    protected static function activityLogHiddenAttributes(): array
    {
        return [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        $logName = property_exists($this, 'activityLogName')
            ? (string) $this->activityLogName
            : 'default';

        return LogOptions::defaults()
            ->useLogName($logName)
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->logExcept(static::activityLogHiddenAttributes());
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        ActivityLogService::enrichActivity($activity);
    }
}
