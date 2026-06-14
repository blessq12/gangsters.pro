<?php

namespace App\Domain\Company\ValueObject;

final readonly class WorkScheduleRow
{
    public function __construct(
        private string $day,
        private ?string $work,
        private bool $isDayOff,
    ) {}

    public function day(): string
    {
        return $this->day;
    }

    public function work(): ?string
    {
        return $this->work;
    }

    public function isDayOff(): bool
    {
        return $this->isDayOff;
    }
}
