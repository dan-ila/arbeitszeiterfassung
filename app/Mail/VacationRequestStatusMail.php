<?php

namespace App\Mail;

use App\Models\Vacation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VacationRequestStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public Vacation $vacation;

    public function __construct(Vacation $vacation)
    {
        $this->vacation = $vacation;
    }

    public function envelope(): Envelope
    {
        $status = strtolower((string) ($this->vacation->status ?? ''));

        $subject = match ($status) {
            'approved' => 'Urlaubsantrag genehmigt',
            'rejected' => 'Urlaubsantrag abgelehnt',
            default => 'Update zu deinem Urlaubsantrag',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vacationRequestStatus',
            with: [
                'vacation' => $this->vacation,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
