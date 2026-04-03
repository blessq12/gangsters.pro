<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Notifications\Ports\ClientOutboundNotifier;

/**
 * Доставка во все подключённые каналы (почта + заглушки и т.д.).
 */
final class CompositeClientOutboundNotifier implements ClientOutboundNotifier
{
    public function __construct(
        private readonly ClientOutboundNotifier $mail,
        private readonly ClientOutboundNotifier $telegram,
        private readonly ClientOutboundNotifier $sms,
    ) {
    }

    public function sendPasswordResetLink(string $email, string $plainToken): void
    {
        $this->mail->sendPasswordResetLink($email, $plainToken);
        $this->telegram->sendPasswordResetLink($email, $plainToken);
        $this->sms->sendPasswordResetLink($email, $plainToken);
    }

    public function sendOrderCreatedConfirmation(string $email, array $orderPayload): void
    {
        $this->mail->sendOrderCreatedConfirmation($email, $orderPayload);
        $this->telegram->sendOrderCreatedConfirmation($email, $orderPayload);
        $this->sms->sendOrderCreatedConfirmation($email, $orderPayload);
    }

    public function sendProfileUpdated(string $email, array $clientPayload): void
    {
        $this->mail->sendProfileUpdated($email, $clientPayload);
        $this->telegram->sendProfileUpdated($email, $clientPayload);
        $this->sms->sendProfileUpdated($email, $clientPayload);
    }

    public function sendRegistrationWelcome(string $email, string $name): void
    {
        $this->mail->sendRegistrationWelcome($email, $name);
        $this->telegram->sendRegistrationWelcome($email, $name);
        $this->sms->sendRegistrationWelcome($email, $name);
    }
}
