<?php

namespace App\Infrastructure\Client\Notification;

use App\Domain\Client\Port\ClientPasswordResetNotifierPort;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class ClientPasswordResetNotifier implements ClientPasswordResetNotifierPort
{
    public function notify(string $email, string $plainToken): void
    {
        $frontendUrl = rtrim((string) config('app.client_frontend_url'), '/');
        $resetUrl = $frontendUrl.'/reset-password?token='.urlencode($plainToken);

        if (config('mail.default') === 'log') {
            Log::info('Client password reset link', [
                'email' => $email,
                'url' => $resetUrl,
            ]);

            return;
        }

        Mail::raw(
            "Ссылка для сброса пароля: {$resetUrl}",
            static function ($message) use ($email): void {
                $message->to($email)
                    ->subject('Сброс пароля — '.config('app.name'));
            },
        );
    }
}
