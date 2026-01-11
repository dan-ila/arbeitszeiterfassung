<?php

namespace App\Mail;

use App\Models\Vacation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewVacationRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Vacation $vacation;

    public function __construct(Vacation $vacation)
    {
        $this->vacation = $vacation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Neuer Urlaubsantrag');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.adminNewVacationRequest',
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
