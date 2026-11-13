<?php

namespace App\Application\Staff\Command;

/**
 * Заглушка восстановления пароля сотрудника.
 */
final class RequestEmployeePasswordResetUseCase
{
    /**
     * @return array{message: string}
     */
    public function execute(string $email): array
    {
        unset($email);

        return [
            'message' => 'Если аккаунт существует, инструкции появятся здесь позже.',
        ];
    }
}
