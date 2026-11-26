<?php

namespace App\Infrastructure\Delivery\Adapter;

use App\Domain\Core\Delivery\Routing\Port\DeliveryBranchLookupPort;
use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Repository\BranchRepository;

final class EloquentDeliveryBranchLookupPort implements DeliveryBranchLookupPort
{
    public function __construct(
        private readonly BranchRepository $branches,
    ) {}

    public function assertBranchExists(int $branchId): void
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }
    }

    public function findAddressByBranchId(int $branchId): ?array
    {
        $branch = $this->branches->findById($branchId);
        if (! $branch instanceof Branch) {
            return null;
        }

        $parts = array_values(array_filter([
            $branch->city(),
            $branch->street(),
            $branch->house(),
        ], static fn (?string $part): bool => $part !== null && trim($part) !== ''));

        return [
            'city' => $branch->city(),
            'street' => $branch->street(),
            'house' => $branch->house(),
            'comment' => $branch->comment(),
            'search_line' => $parts !== [] ? implode(', ', $parts) : null,
            'latitude' => $branch->latitude(),
            'longitude' => $branch->longitude(),
        ];
    }
}
