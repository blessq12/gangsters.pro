<?php

namespace App\Domain\Checkout\Port;

final readonly class RegisteredClientProfileQuote
{
    public function __construct(
        private string $name,
        private string $phone,
        private ?string $email,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }
}
