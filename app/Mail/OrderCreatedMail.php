<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OrderCreatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  list<string>  $lineSummaries
     */
    public function __construct(
        public readonly int $orderId,
        public readonly string $clientName,
        public readonly array $lineSummaries,
        public readonly int $totalRubles,
        public readonly string $deliveryMethod,
        public readonly string $paymentMethod,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Заказ №'.$this->orderId.' принят',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.html.order-created',
            text: 'mail.order-created',
            with: [
                'orderId' => $this->orderId,
                'clientName' => $this->clientName,
                'lineSummaries' => $this->lineSummaries,
                'totalRubles' => $this->totalRubles,
                'deliveryMethod' => $this->deliveryMethod,
                'paymentMethod' => $this->paymentMethod,
            ],
        );
    }
}
