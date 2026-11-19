<?php

namespace App\Domain\Supporting\Promotion\Repository;

use App\Domain\Supporting\Promotion\Entity\Campaign;

interface CampaignRepository
{
    /**
     * @return list<Campaign>
     */
    public function findActiveOrdered(): array;

    /**
     * @return list<Campaign>
     */
    public function findAllOrdered(): array;

    public function save(?int $id, string $title, ?string $body, bool $isActive, ?string $imagePath): Campaign;
}
