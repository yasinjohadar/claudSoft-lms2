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
        'timezone',
        'days_of_week',
        'day_of_month',
        'storage_drivers',
        'storage_config_id',
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
     * وجهة التخزين المختارة لهذه الجدولة
     */
    public function storageConfig()
    {
        return $this->belongsTo(AppStorageConfig::class, 'storage_config_id');
    }

    /**
     * التوقيت الذي يُفسَّر به حقل time.
     * فارغ = توقيت التطبيق (توافقاً مع الجدولات المنشأة قبل إضافة الحقل).
     */
    public function scheduleTimezone(): string
    {
        $tz = trim((string) ($this->timezone ?? ''));

        return $tz !== '' ? $tz : (string) config('app.timezone', 'UTC');
    }

    /**
     * الآن، بتوقيت الجدولة
     */
    private function nowInScheduleTimezone(): Carbon
    {
        return now()->setTimezone($this->scheduleTimezone());
    }

    /**
     * تحويل لحظة محسوبة بتوقيت الجدولة إلى توقيت التطبيق قبل التخزين،
     * لأن shouldRun() تقارنها بـ now() بتوقيت التطبيق.
     */
    private function toAppTimezone(Carbon $moment): Carbon
    {
        return $moment->setTimezone((string) config('app.timezone', 'UTC'));
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
        $now = $this->nowInScheduleTimezone();
        $candidate = $now->copy()->setTimeFromTimeString((string) $this->time);

        if ($candidate->lte($now)) {
            $candidate->addDay();
        }

        return $this->toAppTimezone($candidate);
    }

    /**
     * حساب وقت التشغيل الأسبوعي التالي
     */
    private function calculateNextWeeklyRun(): Carbon
    {
        $now = $this->nowInScheduleTimezone();
        $daysOfWeek = collect($this->days_of_week ?? [])->map(fn ($d) => (int) $d)->sort()->values();

        if ($daysOfWeek->isEmpty()) {
            return $this->toAppTimezone(
                $now->copy()->addWeek()->setTimeFromTimeString((string) $this->time)
            );
        }

        $currentDay = $now->dayOfWeek;

        // اليوم ضمن الأيام المحددة والوقت لم يفت بعد
        if ($daysOfWeek->contains($currentDay)) {
            $todayAt = $now->copy()->setTimeFromTimeString((string) $this->time);
            if ($todayAt->gt($now)) {
                return $this->toAppTimezone($todayAt);
            }
        }

        foreach ($daysOfWeek as $day) {
            if ($day > $currentDay) {
                return $this->toAppTimezone(
                    $now->copy()->next($this->getDayName($day))->setTimeFromTimeString((string) $this->time)
                );
            }
        }

        $firstDay = $daysOfWeek->first();

        return $this->toAppTimezone(
            $now->copy()->next($this->getDayName($firstDay))->setTimeFromTimeString((string) $this->time)
        );
    }

    /**
     * حساب وقت التشغيل الشهري التالي
     */
    private function calculateNextMonthlyRun(): Carbon
    {
        $now = $this->nowInScheduleTimezone();
        $dayOfMonth = (int) ($this->day_of_month ?? 1);

        $nextRun = $this->monthlyOccurrence($now, $dayOfMonth);

        if ($nextRun->lte($now)) {
            $nextRun = $this->monthlyOccurrence($now->copy()->addMonthNoOverflow(), $dayOfMonth);
        }

        return $this->toAppTimezone($nextRun);
    }

    /**
     * اليوم المطلوب من شهر $reference. يوم 31 في شهر من 30 يوماً يُثبَّت على آخر
     * يوم فيه بدل أن ينزلق إلى الشهر التالي كما كان يفعل day() سابقاً.
     */
    private function monthlyOccurrence(Carbon $reference, int $dayOfMonth): Carbon
    {
        $day = min(max($dayOfMonth, 1), $reference->daysInMonth);

        return $reference->copy()->day($day)->setTimeFromTimeString((string) $this->time);
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
