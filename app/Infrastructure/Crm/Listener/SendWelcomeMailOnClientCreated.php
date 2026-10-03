<?php

namespace App\Infrastructure\Crm\Listener;

use App\Domain\Crm\Event\ClientCreated;
use App\Mail\ClientWelcomeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * CRM: приветственное письмо после регистрации (если есть email).
 */
final class SendWelcomeMailOnClientCreated
{
    public function handle(ClientCreated $event): void
    {
        $email = trim((string) ($event->email() ?? ''));
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new ClientWelcomeMail(
                clientName: $event->name(),
            ));
        } catch (Throwable $exception) {
            Log::error('Не удалось отправить приветственное письмо', [
                'client_id' => $event->clientId(),
                'email' => $email,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
