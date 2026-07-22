<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BackupSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'backup_type',
        'frequency',
        'time',
        'days_of_week',
        'day_of_month',
        'storage_drivers',
        'compression_types',
        'retention_days',
        'is_active',
        'last_run_at',
        'next_run_at',
        'created_by',
    ];

    protected $casts = [
        'time' => 'string',
        'days_of_week' => 'array',
        'storage_drivers' => 'array',
        'compression_types' => 'array',
        'retention_days' => 'integer',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * أنواع المحتوى
     */
    public const BACKUP_TYPES = [
        'full' => 'كامل',
        'database' => 'قاعدة البيانات',
        'files' => 'الملفات',
        'config' => 'الإعدادات',
    ];

    /**
     * التكرارات
     */
    public const FREQUENCIES = [
        'daily' => 'يومي',
        'weekly' => 'أسبوعي',
        'monthly' => 'شهري',
        'custom' => 'مخصص',
    ];

    /**
     * العلاقة مع منشئ الجدولة
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع النسخ
     */
    public function backups()
    {
        return $this->hasMany(Backup::class, 'schedule_id');
    }

    /**
     * التحقق من وجوب التشغيل
     */
    public function shouldRun(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->next_run_at) {
            return false;
        }

        return now()->gte($this->next_run_at);
    }

    /**
     * حساب وقت التشغيل التالي
     */
    public function calculateNextRun(): Carbon
    {
        return match ($this->frequency) {
            'daily', 'custom' => $this->nextDailyOccurrence(),
            'weekly' => $this->calculateNextWeeklyRun(),
            'monthly' => $this->calculateNextMonthlyRun(),
            default => $this->nextDailyOccurrence(),
        };
    }

    /**
     * اليوم في الوقت المحدد إن لم يفت، وإلا غداً.
     */
    private function nextDailyOccurrence(): Carbon
    {
        $candidate = now()->copy()->setTimeFromTimeString((string) $this->time);

        if ($candidate->lte(now())) {
            $candidate->addDay();
        }

        return $candidate;
    }

    /**
     * حساب وقت التشغيل الأسبوعي التالي
     */
    private function calculateNextWeeklyRun(): Carbon
    {
        $now = now();
        $daysOfWeek = collect($this->days_of_week ?? [])->map(fn ($d) => (int) $d)->sort()->values();

        if ($daysOfWeek->isEmpty()) {
            return $now->copy()->addWeek()->setTimeFromTimeString((string) $this->time);
        }

        $currentDay = $now->dayOfWeek;

        // اليوم ضمن الأيام المحددة والوقت لم يفت بعد
        if ($daysOfWeek->contains($currentDay)) {
            $todayAt = $now->copy()->setTimeFromTimeString((string) $this->time);
            if ($todayAt->gt($now)) {
                return $todayAt;
            }
        }

        foreach ($daysOfWeek as $day) {
            if ($day > $currentDay) {
                return $now->copy()->next($this->getDayName($day))->setTimeFromTimeString((string) $this->time);
            }
        }

        $firstDay = $daysOfWeek->first();

        return $now->copy()->next($this->getDayName($firstDay))->setTimeFromTimeString((string) $this->time);
    }

    /**
     * حساب وقت التشغيل الشهري التالي
     */
    private function calculateNextMonthlyRun(): Carbon
    {
        $time = Carbon::parse($this->time);
        $now = now();
        $dayOfMonth = $this->day_of_month ?? 1;

        $nextRun = $now->copy()->day($dayOfMonth)->setTimeFromTimeString($this->time);

        if ($nextRun->isPast()) {
            $nextRun->addMonth();
        }

        return $nextRun;
    }

    /**
     * الحصول على اسم اليوم
     */
    private function getDayName(int $day): string
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return $days[$day] ?? 'Monday';
    }

    /**
     * تنفيذ الجدولة
     */
    public function execute(): Backup
    {
        // سيتم تنفيذ هذا في Service
        throw new \Exception('Not implemented');
    }
}
