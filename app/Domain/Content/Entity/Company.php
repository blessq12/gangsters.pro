<?php

namespace App\Domain\Content\Entity;

use App\Domain\Content\ValueObject\CompanySocials;

/**
 * Публичный профиль компании.
 */
final class Company
{
    /**
     * @param  array<string, array{work: ?string, is_day_off: bool}>  $schedule
     */
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly ?string $description,
        private readonly ?string $phone,
        private readonly ?string $email,
        private readonly CompanySocials $socials,
        private readonly array $schedule,
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

    /**
     * @return array<string, array{work: ?string, is_day_off: bool}>
     */
    public function schedule(): array
    {
        return $this->schedule;
    }
}
