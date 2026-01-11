<?php

namespace App\Mail;

use App\Models\WorkTimeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewWorkTimeRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public WorkTimeRequest $requestModel;

    public function __construct(WorkTimeRequest $requestModel)
    {
        $this->requestModel = $requestModel;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Neue Arbeitszeit-Anfrage');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.adminNewWorkTimeRequest',
            with: [
                'req' => $this->requestModel,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
