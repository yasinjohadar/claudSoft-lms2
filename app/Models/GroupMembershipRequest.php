<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMembershipRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id',
        'student_id',
        'status',
        'terms_accepted',
        'payment_date',
        'message',
        'admin_notes',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'whatsapp_invite_sent_at',
        'whatsapp_invite_sent_by',
        'email_invite_sent_at',
        'email_invite_sent_by',
    ];

    protected $casts = [
        'terms_accepted' => 'boolean',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'whatsapp_invite_sent_at' => 'datetime',
        'email_invite_sent_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the group that the request is for.
     */
    public function group()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    /**
     * Get the student who made the request.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the admin who approved the request.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the admin who rejected the request.
     */
    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function whatsappInviteSender()
    {
        return $this->belongsTo(User::class, 'whatsapp_invite_sent_by');
    }

    public function emailInviteSender()
    {
        return $this->belongsTo(User::class, 'email_invite_sent_by');
    }

    public function hasPendingWhatsAppInvite(): bool
    {
        return $this->whatsapp_invite_sent_at !== null;
    }

    public function hasEmailInviteSent(): bool
    {
        return $this->email_invite_sent_at !== null;
    }

    public function markWhatsAppInviteSent(?int $sentBy = null): bool
    {
        return $this->update([
            'whatsapp_invite_sent_at' => now(),
            'whatsapp_invite_sent_by' => $sentBy ?? auth()->id(),
        ]);
    }

    public function markEmailInviteSent(?int $sentBy = null): bool
    {
        return $this->update([
            'email_invite_sent_at' => now(),
            'email_invite_sent_by' => $sentBy ?? auth()->id(),
        ]);
    }

    // Scopes

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to filter by student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to filter by group.
     */
    public function scopeForGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    // Helper Methods

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the request.
     */
    public function approve(?int $approvedBy = null): bool
    {
        $approvedById = $approvedBy ?? auth()->id();

        // إعادة تعيين حقول الرفض إذا كان الطلب مرفوض مسبقاً
        $updateData = [
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedById,
        ];

        // إذا كان الطلب مرفوض مسبقاً، نعيد تعيين حقول الرفض
        if ($this->isRejected()) {
            $updateData['rejected_at'] = null;
            $updateData['rejected_by'] = null;
        }

        $result = $this->update($updateData);

        // Add student to group if approval is successful
        if ($result) {
            // Refresh relations to ensure they're loaded
            $this->load(['group', 'student']);
            
            if ($this->group && $this->student) {
                $this->group->addMember($this->student, 'member', [
                    'source' => \App\Models\CourseGroupMembershipHistory::SOURCE_MEMBERSHIP_REQUEST,
                    'source_reference_id' => $this->id,
                    'performed_by' => $approvedById,
                ]);
            }
        }

        return $result;
    }

    /**
     * Reject the request.
     */
    public function reject(?int $rejectedBy = null, ?string $adminNotes = null): bool
    {
        $rejectedById = $rejectedBy ?? auth()->id();

        return $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $rejectedById,
            'admin_notes' => $adminNotes ?? $this->admin_notes,
        ]);
    }

    /**
     * Find the group registration form submission linked to this request.
     */
    public function resolveRegistration(): ?GroupRegistration
    {
        $this->loadMissing('student');

        $query = GroupRegistration::query()
            ->where('group_id', $this->group_id)
            ->with('nationality');

        if ($this->student_id) {
            $byUser = (clone $query)->where('user_id', $this->student_id)->latest()->first();
            if ($byUser) {
                return $byUser;
            }
        }

        $email = $this->student?->email;
        if ($email) {
            return $query->where('email', $email)->latest()->first();
        }

        return null;
    }
}
