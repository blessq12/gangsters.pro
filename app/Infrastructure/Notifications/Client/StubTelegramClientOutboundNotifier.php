<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use Illuminate\Support\Facades\Log;

/**
 * Заглушка: клиентский Telegram (нужен chat_id в профиле и отдельный поток доставки).
 */
final class StubTelegramClientOutboundNotifier implements ClientOutboundNotifier
{
    public function sendPasswordResetLink(string $email, string $plainToken): void
    {
        $this->log('password_reset', $email, ['has_token' => $plainToken !== '']);
    }

    public function sendOrderCreatedConfirmation(string $email, array $orderPayload): void
    {
        $this->log('order_created', $email, [
            'order_id' => $orderPayload['id'] ?? null,
        ]);
    }

    public function sendProfileUpdated(string $email, array $clientPayload): void
    {
        $this->log('profile_updated', $email, [
            'client_id' => $clientPayload['id'] ?? null,
        ]);
    }

    public function sendRegistrationWelcome(string $email, string $name): void
    {
        $this->log('registration_welcome', $email, ['name' => $name]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $intent, string $email, array $context = []): void
    {
        Log::debug('notifications.client.telegram_stub', array_merge([
            'intent' => $intent,
            'email' => $email,
        ], $context));
    }
}
