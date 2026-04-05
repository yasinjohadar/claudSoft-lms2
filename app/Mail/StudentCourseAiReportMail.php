<?php

namespace App\Mail;

use App\Models\StudentCourseAiReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentCourseAiReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StudentCourseAiReport $report)
    {
        $this->report->loadMissing(['course', 'courseGroup']);
    }

    public function envelope(): Envelope
    {
        $title = $this->report->course?->title ?? 'الكورس';
        $group = $this->report->courseGroup?->name;
        $suffix = $group ? (' — '.$group) : '';

        return new Envelope(
            subject: 'تقرير الدراسة — '.$title.$suffix,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.student-course-ai-report',
            with: [
                'report' => $this->report,
                'url' => route('student.progress.ai-reports.show', $this->report),
            ],
        );
    }
}
