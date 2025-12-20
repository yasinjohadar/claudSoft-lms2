<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Certificate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'certificate_number',
        'user_id',
        'course_id',
        'course_enrollment_id',
        'certificate_template_id',
        'issued_by',
        'student_name',
        'course_name',
        'course_name_en',
        'issue_date',
        'completion_date',
        'expiry_date',
        'completion_percentage',
        'attendance_percentage',
        'final_exam_score',
        'course_hours',
        'verification_code',
        'pdf_path',
        'qr_code_path',
        'metadata',
        'status',
        'revocation_reason',
        'revoked_at',
        'revoked_by',
        'download_count',
        'last_downloaded_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'completion_date' => 'date',
        'expiry_date' => 'date',
        'completion_percentage' => 'decimal:2',
        'attendance_percentage' => 'decimal:2',
        'final_exam_score' => 'decimal:2',
        'metadata' => 'array',
        'revoked_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('expiry_date', '<', now())
                    ->where('status', 'active');
            });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByVerificationCode($query, $code)
    {
        return $query->where('verification_code', $code);
    }

    // Accessors & Mutators
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsRevokedAttribute(): bool
    {
        return $this->status === 'revoked';
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            $this->update(['status' => 'expired']);
            return true;
        }

        return false;
    }

    public function getVerificationUrlAttribute(): string
    {
        return url('/verify-certificate/' . $this->verification_code);
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code_path ? asset('storage/' . $this->qr_code_path) : null;
    }

    // Methods
    public function revoke(string $reason, int $revokedBy): bool
    {
        return $this->update([
            'status' => 'revoked',
            'revocation_reason' => $reason,
            'revoked_at' => now(),
            'revoked_by' => $revokedBy,
        ]);
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    public function checkExpiry(): void
    {
        if ($this->expiry_date && $this->expiry_date->isPast() && $this->status === 'active') {
            $this->update(['status' => 'expired']);
        }
    }

    public function isValid(): bool
    {
        return $this->status === 'active' &&
               (!$this->expiry_date || $this->expiry_date->isFuture());
    }

    public function canBeDownloaded(): bool
    {
        return $this->isValid() && $this->pdf_path && file_exists(storage_path('app/public/' . $this->pdf_path));
    }

    // Static Methods
    public static function generateCertificateNumber(): string
    {
        $prefix = config('certificate.certificate_prefix', 'CERT');
        $year = date('Y');

        $lastCertificate = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastCertificate ? (int)substr($lastCertificate->certificate_number, -5) + 1 : 1;

        return sprintf('%s-%s-%05d', $prefix, $year, $number);
    }

    public static function generateVerificationCode(): string
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(16)));
        } while (static::where('verification_code', $code)->exists());

        return $code;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (!$certificate->certificate_number) {
                $certificate->certificate_number = static::generateCertificateNumber();
            }

            if (!$certificate->verification_code) {
                $certificate->verification_code = static::generateVerificationCode();
            }

            if (!$certificate->issue_date) {
                $certificate->issue_date = now();
            }
        });
    }
}
