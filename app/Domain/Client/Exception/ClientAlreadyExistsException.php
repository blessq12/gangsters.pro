<?php

namespace App\Domain\Client\Exception;

use RuntimeException;

final class ClientAlreadyExistsException extends RuntimeException
{
    public static function byPhone(): self
    {
        return new self('Клиент с таким телефоном уже зарегистрирован.');
    }

    public static function byEmail(): self
    {
        return new self('Клиент с таким email уже зарегистрирован.');
    }
}
