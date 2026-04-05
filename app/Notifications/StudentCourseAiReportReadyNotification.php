<?php

namespace App\Notifications;

use App\Mail\StudentCourseAiReportMail;
use App\Models\StudentCourseAiReport;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentCourseAiReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public StudentCourseAiReport $report)
    {
        $this->report->loadMissing(['course', 'courseGroup']);
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $settings = app(WhatsAppSettingsService::class)->getSettings();
        $delivery = $settings['study_report_delivery'] ?? 'both';
        if (! in_array($delivery, ['email', 'whatsapp', 'both'], true)) {
            $delivery = 'both';
        }

        $channels = ['database'];

        $waGloballyOn = ! empty($settings['whatsapp_enabled']);
        $hasWa = ! empty($notifiable->whatsapp_number);
        $whatsappOk = $waGloballyOn && $hasWa;
        $hasEmail = ! empty($notifiable->email);

        $wantEmail = in_array($delivery, ['email', 'both'], true);
        $wantWhatsApp = in_array($delivery, ['whatsapp', 'both'], true);

        if ($delivery === 'whatsapp' && ! $whatsappOk && $hasEmail) {
            $wantEmail = true;
        }

        if ($wantEmail && $hasEmail) {
            $channels[] = 'mail';
        }

        if ($wantWhatsApp && $whatsappOk) {
            $channels[] = 'whatsapp';
        }

        return array_values(array_unique($channels));
    }

    public function toMail(object $notifiable): StudentCourseAiReportMail
    {
        return (new StudentCourseAiReportMail($this->report))->to($notifiable->email);
    }

    public function toWhatsApp(object $notifiable): string
    {
        $url = route('student.progress.ai-reports.show', $this->report, absolute: true);
        $course = $this->report->course?->title ?? 'الكورس';
        $lines = [
            'مرحباً،',
            'تقرير دراسة جديد جاهز للكورس: '.$course,
        ];
        if ($this->report->courseGroup?->name) {
            $lines[] = 'المجموعة: '.$this->report->courseGroup->name;
        }
        $lines[] = 'للاطلاع على التقرير: '.$url;

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'course_id' => $this->report->course_id,
            'course_title' => $this->report->course?->title,
            'message' => 'تقرير دراسة جديد متاح للاطلاع.',
            'group_name' => $this->report->courseGroup?->name,
        ];
    }
}
