<?php

namespace App\Mail;

use App\Models\WorkTimeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkTimeRequestStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public WorkTimeRequest $requestModel;

    public function __construct(WorkTimeRequest $requestModel)
    {
        $this->requestModel = $requestModel;
    }

    public function envelope(): Envelope
    {
        $status = strtolower((string) ($this->requestModel->status ?? ''));

        $subject = match ($status) {
            'approved' => 'Arbeitszeit-Anfrage genehmigt',
            'rejected' => 'Arbeitszeit-Anfrage abgelehnt',
            default => 'Update zu deiner Arbeitszeit-Anfrage',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.workTimeRequestStatus',
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
