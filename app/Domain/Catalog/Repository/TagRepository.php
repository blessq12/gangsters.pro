<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\Tag;

interface TagRepository
{
    /**
     * @return list<Tag>
     */
    public function findAllActiveOrdered(): array;

    public function findById(int $id): ?Tag;

    /**
     * @param  list<int>  $ids
     * @return list<Tag>
     */
    public function findByIds(array $ids): array;

    /**
     * @return list<int>
     */
    public function findTagIdsByProductId(int $productId): array;

    /**
     * @return list<int>
     */
    public function findTagIdsBySetId(int $setId): array;
}
