<?php

namespace App\Application\Staff\Command;

/**
 * Заглушка: письмо сброса пароля подключим позже.
 */
final class RequestEmployeePasswordResetUseCase
{
    /**
     * @return array{message: string}
     */
    public function execute(?string $email): array
    {
        $email = trim((string) ($email ?? ''));
        if ($email === '') {
            throw new \InvalidArgumentException('Email обязателен.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email.');
        }

        return [
            'message' => 'Если такой аккаунт есть, мы отправим инструкцию по восстановлению пароля.',
        ];
    }
}
