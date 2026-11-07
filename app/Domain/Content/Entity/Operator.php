<?php

namespace App\Domain\Content\Entity;

final class Operator
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $email,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }
}
