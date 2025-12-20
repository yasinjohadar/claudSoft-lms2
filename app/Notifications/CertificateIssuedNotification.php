<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $certificate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎓 مبروك! تم إصدار شهادتك')
            ->greeting('مرحباً ' . $this->certificate->student_name . '!')
            ->line('تهانينا! 🎉 تم إصدار شهادة إتمام الكورس بنجاح.')
            ->line('**الكورس:** ' . $this->certificate->course_name)
            ->line('**رقم الشهادة:** ' . $this->certificate->certificate_number)
            ->line('**تاريخ الإصدار:** ' . $this->certificate->issue_date->format('Y-m-d'))
            ->action('عرض الشهادة', route('student.certificates.show', $this->certificate->id))
            ->line('يمكنك تحميل الشهادة ومشاركتها مع الآخرين.')
            ->line('شكراً لجهودك وإنجازك الرائع! 🌟')
            ->salutation('مع أطيب التحيات، فريق ' . config('app.name'));
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
            'course_name' => $this->certificate->course_name,
            'message' => 'تم إصدار شهادة إتمام الكورس: ' . $this->certificate->course_name,
            'action_url' => route('student.certificates.show', $this->certificate->id),
            'icon' => 'certificate',
            'type' => 'certificate_issued',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
            'course_name' => $this->certificate->course_name,
        ];
    }
}
