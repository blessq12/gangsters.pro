<?php

namespace App\Application\Client\Command;

/**
 * Заглушка: письмо сброса пароля подключим позже.
 */
final class RequestPasswordResetUseCase
{
    /**
     * @return array{message: string}
     */
    public function execute(?string $phone, ?string $email): array
    {
        $phone = trim((string) ($phone ?? ''));
        $email = trim((string) ($email ?? ''));

        if ($phone === '' && $email === '') {
            throw new \InvalidArgumentException('Нужен phone или email.');
        }

        return [
            'message' => 'Если такой аккаунт есть, мы отправим инструкцию по восстановлению пароля.',
        ];
    }
}
