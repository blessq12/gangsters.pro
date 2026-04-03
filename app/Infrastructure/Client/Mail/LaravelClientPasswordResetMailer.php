<?php

namespace App\Infrastructure\Client\Mail;

use App\Application\Client\Ports\ClientPasswordResetMailer;
use App\Mail\ClientPasswordResetMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class LaravelClientPasswordResetMailer implements ClientPasswordResetMailer
{
    public function sendResetLink(string $email, string $token): void
    {
        $base = rtrim((string) config('app.client_frontend_url'), '/');
        $resetUrl = $base.'/reset-password?token='.rawurlencode($token);

        try {
            Mail::to($email)->send(new ClientPasswordResetMail($resetUrl));
        } catch (\Throwable $e) {
            Log::error('client.password_reset.mail_failed', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
