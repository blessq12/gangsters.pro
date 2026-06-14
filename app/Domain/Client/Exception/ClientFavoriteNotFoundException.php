<?php

namespace App\Domain\Client\Exception;

use RuntimeException;

final class ClientFavoriteNotFoundException extends RuntimeException
{
    public static function forProductId(int $productId): self
    {
        return new self(sprintf('Товар %d не найден в избранном.', $productId));
    }
}
