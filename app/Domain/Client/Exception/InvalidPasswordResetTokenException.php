<?php

namespace App\Domain\Client\Exception;

use RuntimeException;

final class InvalidPasswordResetTokenException extends RuntimeException
{
    public static function expiredOrInvalid(): self
    {
        return new self('Ссылка для сброса пароля недействительна или устарела.');
    }
}
