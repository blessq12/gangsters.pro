<?php

namespace App\Domain\Content\ValueObject;

final readonly class CompanyContact
{
    public function __construct(
        private ?string $phone,
        private ?string $phoneAdditional,
        private ?string $supportPhone,
        private ?string $whatsappPhone,
        private ?string $emailAddress,
        private ?string $publicEmail,
    ) {}

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function phoneAdditional(): ?string
    {
        return $this->phoneAdditional;
    }

    public function supportPhone(): ?string
    {
        return $this->supportPhone;
    }

    public function whatsappPhone(): ?string
    {
        return $this->whatsappPhone;
    }

    public function emailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function publicEmail(): ?string
    {
        return $this->publicEmail;
    }
}
