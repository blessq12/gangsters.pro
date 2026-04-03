<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ClientWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $name,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Добро пожаловать в Gangsters',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-welcome',
        );
    }
}
