<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseCompletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Enrollment $enrollment,
        public readonly Certificate $certificate,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.completion_subject', ['course' => $this->enrollment->course->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.course-completion',
        );
    }
}
