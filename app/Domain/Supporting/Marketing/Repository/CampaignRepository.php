<?php

namespace App\Domain\Supporting\Marketing\Repository;

use App\Domain\Supporting\Marketing\Entity\Campaign;

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

    /**
     * @return list<Campaign>
     */
    public function listPage(int $offset, int $limit): array;

    public function countAll(): int;

    public function findById(int $id): ?Campaign;

    /** Сырой путь картинки в storage (для замены файла). */
    public function findStoredImagePath(int $id): ?string;

    public function save(?int $id, string $title, ?string $body, bool $isActive, ?string $imagePath): Campaign;
}
