<?php

namespace App\Domain\Supporting\Company\Entity;

use App\Domain\Supporting\Company\ValueObject\CompanySocials;

/**
 * Публичный профиль компании.
 * Расписание работы — у филиала (Branch), не здесь.
 */
final class Company
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly ?string $description,
        private readonly ?string $phone,
        private readonly ?string $email,
        private readonly CompanySocials $socials,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function socials(): CompanySocials
    {
        return $this->socials;
    }
}
