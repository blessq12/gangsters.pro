<?php

namespace App\Domain\AggregatorIngress\Exception;

use RuntimeException;

final class IngressInvariantViolation extends RuntimeException
{
    public static function invalidPayload(string $message): self
    {
        return new self($message);
    }
}
