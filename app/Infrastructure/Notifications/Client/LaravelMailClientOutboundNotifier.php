<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Mail\ClientOrderConfirmationMail;
use App\Mail\ClientPasswordResetMail;
use App\Mail\ClientProfileUpdatedMail;
use App\Mail\ClientWelcomeMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Канал доставки: электронная почта.
 */
final class LaravelMailClientOutboundNotifier implements ClientOutboundNotifier
{
    public function sendPasswordResetLink(string $email, string $plainToken): void
    {
        $base = rtrim((string) config('app.client_frontend_url'), '/');
        $resetUrl = $base.'/reset-password?token='.rawurlencode($plainToken);

        $this->sendSafely(
            $email,
            new ClientPasswordResetMail($resetUrl),
            'notifications.client.password_reset.mail_failed',
        );
    }

    public function sendOrderCreatedConfirmation(string $email, array $orderPayload): void
    {
        $this->sendSafely(
            $email,
            new ClientOrderConfirmationMail($orderPayload),
            'notifications.client.order_confirmation.mail_failed',
        );
    }

    public function sendProfileUpdated(string $email, array $clientPayload): void
    {
        $this->sendSafely(
            $email,
            new ClientProfileUpdatedMail($clientPayload),
            'notifications.client.profile_updated.mail_failed',
        );
    }

    public function sendRegistrationWelcome(string $email, string $name): void
    {
        $this->sendSafely(
            $email,
            new ClientWelcomeMail($name),
            'notifications.client.welcome.mail_failed',
        );
    }

    private function sendSafely(string $email, Mailable $mailable, string $logKey): void
    {
        try {
            Mail::to($email)->send($mailable);
        } catch (\Throwable $e) {
            Log::error($logKey, [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
