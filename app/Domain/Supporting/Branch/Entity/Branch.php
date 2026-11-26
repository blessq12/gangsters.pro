<?php

namespace App\Domain\Supporting\Branch\Entity;

/**
 * Филиал компании — единственная физическая точка (производство и выдача).
 * Адрес, координаты и расписание живут только здесь; другие BC хранят branchId.
 */
final class Branch
{
    /** Компания-синглтон в текущей модели продукта. */
    public const DEFAULT_COMPANY_ID = 1;

    /**
     * @param  array<string, array{work: ?string, is_day_off: bool}>  $schedule
     */
    public function __construct(
        private readonly int $id,
        private readonly int $companyId,
        private readonly string $name,
        private readonly bool $isActive,
        private readonly array $schedule,
        private readonly ?string $city = null,
        private readonly ?string $street = null,
        private readonly ?string $house = null,
        private readonly ?string $comment = null,
        private readonly ?float $latitude = null,
        private readonly ?float $longitude = null,
        private readonly ?string $phone = null,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return array<string, array{work: ?string, is_day_off: bool}>
     */
    public function schedule(): array
    {
        return $this->schedule;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function street(): ?string
    {
        return $this->street;
    }

    public function house(): ?string
    {
        return $this->house;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function latitude(): ?float
    {
        return $this->latitude;
    }

    public function longitude(): ?float
    {
        return $this->longitude;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }
}
