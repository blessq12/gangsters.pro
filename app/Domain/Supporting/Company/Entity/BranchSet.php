<?php

namespace App\Domain\Supporting\Company\Entity;

use App\Domain\Supporting\Company\ValueObject\BranchSetLine;

final class BranchSet
{
    /**
     * @param  list<BranchSetLine>  $lines
     * @param  list<int>  $tagIds
     */
    public function __construct(
        private readonly int $id,
        private readonly int $branchId,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?string $description,
        private readonly bool $isAvailable,
        private readonly int $priceKopecks,
        private readonly string $status,
        private readonly array $lines,
        private readonly array $tagIds = [],
    ) {
        if ($this->priceKopecks < 0) {
            throw new \InvalidArgumentException('Цена сета не может быть отрицательной.');
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function priceKopecks(): int
    {
        return $this->priceKopecks;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * @return list<BranchSetLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return list<int>
     */
    public function tagIds(): array
    {
        return $this->tagIds;
    }
}
