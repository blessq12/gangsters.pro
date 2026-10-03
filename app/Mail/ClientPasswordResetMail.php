<?php

namespace App\Mail;

use App\Mail\Support\MailBrandName;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ClientPasswordResetMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $clientName,
        public readonly string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новый пароль для входа',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.html.client-password-reset',
            text: 'mail.client-password-reset',
            with: [
                'clientName' => $this->clientName,
                'plainPassword' => $this->plainPassword,
                'brandName' => MailBrandName::resolve(),
            ],
        );
    }
}
