<?php

namespace App\Domain\Company\ValueObject;

final readonly class CompanySchedule
{
    /**
     * @param  list<WorkScheduleRow>  $workSchedule
     */
    public function __construct(
        private ?string $workHours,
        private array $workSchedule,
    ) {}

    public function workHours(): ?string
    {
        return $this->workHours;
    }

    /**
     * @return list<WorkScheduleRow>
     */
    public function workSchedule(): array
    {
        return $this->workSchedule;
    }
}
