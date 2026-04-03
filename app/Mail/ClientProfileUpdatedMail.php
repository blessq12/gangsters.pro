<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ClientProfileUpdatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $client
     */
    public function __construct(
        public array $client,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Профиль обновлён — Gangsters',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-profile-updated',
        );
    }
}
