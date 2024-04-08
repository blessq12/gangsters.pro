<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GreetingMessageWithPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public function __construct(
        public object $data,
        public string $password
    ) {
        //
    }

    /**
     * Get the message envelope.
     */

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Регистрация на сайте gangsters.pro',
        );
    }

    /**
     * Get the message content definition.
     */

    public function content(): Content
    {
        return new Content(
            view: 'mail.greeting',
            with: [
                'name' => 'Andrew',
                'domain' => 'code70@'
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
