<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ClientOrderConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $order
     */
    public function __construct(
        public array $order,
    ) {}

    public function envelope(): Envelope
    {
        $id = $this->order['id'] ?? '';

        return new Envelope(
            subject: 'Заказ '.$id.' — Gangsters, спасибо!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-order-confirmation',
        );
    }
}
