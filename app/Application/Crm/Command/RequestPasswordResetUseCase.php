<?php

namespace App\Application\Crm\Command;

use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Event\ClientPasswordChanged;
use App\Domain\Crm\Repository\ClientRepository;
use App\Mail\ClientPasswordResetMail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Сценарий: сгенерировать новый пароль и отправить его на email клиента.
 */
final class RequestPasswordResetUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {}

    /**
     * Всегда «успех» с точки зрения API (anti-enumeration),
     * кроме сбоя доставки письма — тогда пароль не меняем.
     *
     * @throws RuntimeException если письмо не удалось отправить
     */
    public function execute(string $email): void
    {
        $email = trim($email);
        if ($email === '') {
            return;
        }

        $client = $this->clients->findByEmail($email);
        if (! $client instanceof Client) {
            return;
        }

        $plainPassword = $this->generatePassword();

        try {
            Mail::to($email)->send(new ClientPasswordResetMail(
                clientName: $client->name(),
                plainPassword: $plainPassword,
            ));
        } catch (Throwable $e) {
            Log::error('Не удалось отправить письмо восстановления пароля', [
                'email' => $email,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw new RuntimeException('Не удалось отправить письмо с новым паролем.', 0, $e);
        }

        $client->changePassword(Hash::make($plainPassword));
        $this->clients->save($client);

        Event::dispatch(ClientPasswordChanged::fromClient($client));
    }

    private function generatePassword(): string
    {
        // Читаемый пароль без неоднозначных символов.
        return Str::password(
            length: 12,
            letters: true,
            numbers: true,
            symbols: false,
            spaces: false,
        );
    }
}
