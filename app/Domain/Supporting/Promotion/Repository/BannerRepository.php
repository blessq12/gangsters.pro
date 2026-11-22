<?php

namespace App\Domain\Supporting\Promotion\Repository;

use App\Domain\Supporting\Promotion\Entity\Banner;

interface BannerRepository
{
    /**
     * @return list<Banner>
     */
    public function findActiveOrdered(): array;

    /**
     * @return list<Banner>
     */
    public function findAllOrdered(): array;

    /**
     * @return list<Banner>
     */
    public function listPage(int $offset, int $limit): array;

    public function countAll(): int;

    public function findById(int $id): ?Banner;

    /**
     * Сырые пути в storage (для замены файлов).
     *
     * @return array{image_desktop: ?string, image_mobile: ?string}|null
     */
    public function findStoredPaths(int $id): ?array;

    public function save(?int $id, bool $isActive, ?string $desktopPath, ?string $mobilePath): Banner;
}
