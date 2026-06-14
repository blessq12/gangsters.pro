<?php

namespace App\Domain\Client\Exception;

use RuntimeException;

final class ClientAddressNotFoundException extends RuntimeException
{
    public static function forId(int $addressId): self
    {
        return new self(sprintf('Адрес %d не найден.', $addressId));
    }
}
