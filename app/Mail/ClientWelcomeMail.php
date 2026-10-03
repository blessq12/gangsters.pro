<?php

namespace App\Mail;

use App\Mail\Support\MailBrandName;
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
        public readonly string $clientName,
    ) {}

    public function envelope(): Envelope
    {
        $brandName = MailBrandName::resolve();

        return new Envelope(
            subject: $brandName !== ''
                ? 'Добро пожаловать в '.$brandName
                : 'Добро пожаловать',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.html.client-welcome',
            text: 'mail.client-welcome',
            with: [
                'clientName' => $this->clientName,
                'brandName' => MailBrandName::resolve(),
            ],
        );
    }
}
