<?php

namespace App\Domain\Order\OrderDraft\ValueObject;

final readonly class GuestContact
{
    public function __construct(
        private string $name,
        private string $phone,
        private ?string $email = null,
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
