<?php

namespace App\Domain\Client\Exception;

use RuntimeException;

final class ClientNotFoundException extends RuntimeException
{
    public static function forId(int $clientId): self
    {
        return new self(sprintf('Клиент %d не найден.', $clientId));
    }

    public static function byPhone(string $phone): self
    {
        return new self('Клиент с таким телефоном не найден.');
    }

    public static function byEmail(string $email): self
    {
        return new self('Клиент с таким email не найден.');
    }

    public static function byCredentials(): self
    {
        return new self('Неверный телефон, email или пароль.');
    }
}
