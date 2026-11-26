<?php

namespace App\Application\Branch\Presenter;

use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Support\ScheduleNormalizer;
use App\Shared\ValueObject\PhoneNumber;

/**
 * Формат ответа филиала для админки и витрины.
 */
final class BranchPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function branch(Branch $branch): array
    {
        return [
            'id' => $branch->id(),
            'company_id' => $branch->companyId(),
            'name' => $branch->name(),
            'is_active' => $branch->isActive(),
            'schedule' => ScheduleNormalizer::normalize($branch->schedule()),
            'city' => $branch->city(),
            'street' => $branch->street(),
            'house' => $branch->house(),
            'comment' => $branch->comment(),
            'latitude' => $branch->latitude(),
            'longitude' => $branch->longitude(),
            'phone' => $this->formatOptionalPhone($branch->phone()),
        ];
    }

    /**
     * @return array{id: int, name: string, is_active: bool, phone: string|null}
     */
    public function branchSummary(Branch $branch): array
    {
        return [
            'id' => $branch->id(),
            'name' => $branch->name(),
            'is_active' => $branch->isActive(),
            'phone' => $this->formatOptionalPhone($branch->phone()),
        ];
    }

    private function formatOptionalPhone(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $phone = PhoneNumber::tryFromRaw($raw);

        return $phone?->formatted() ?? $raw;
    }
}
