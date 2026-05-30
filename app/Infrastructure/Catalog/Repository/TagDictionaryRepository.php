<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Application\Catalog\Contracts\TagDictionaryRepository as TagDictionaryRepositoryContract;
use App\Application\Catalog\DTO\AdminTagDTO;
use App\Application\Catalog\DTO\CreateAdminTagDTO;
use App\Application\Catalog\DTO\UpdateAdminTagDTO;
use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Product\Model\PRD_Tag;

final class TagDictionaryRepository implements TagDictionaryRepositoryContract
{
    public function listAll(): array
    {
        return PRD_Tag::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (PRD_Tag $tag): AdminTagDTO => $this->map($tag))
            ->all();
    }

    public function findById(int $id): ?AdminTagDTO
    {
        $tag = PRD_Tag::query()->find($id);

        return $tag ? $this->map($tag) : null;
    }

    public function create(CreateAdminTagDTO $dto): AdminTagDTO
    {
        $tag = new PRD_Tag([
            'label' => $dto->label,
            'color' => $dto->color,
            'is_active' => $dto->isActive,
            'sort_order' => $dto->sortOrder,
        ]);
        $tag->save();

        return $this->map($tag->refresh());
    }

    public function update(UpdateAdminTagDTO $dto): AdminTagDTO
    {
        $tag = PRD_Tag::query()->find($dto->id);
        if ($tag === null) {
            throw new ApiException('Tag not found.', 404);
        }

        $tag->label = $dto->label;
        $tag->color = $dto->color;
        $tag->is_active = $dto->isActive;
        $tag->sort_order = $dto->sortOrder;
        $tag->save();

        return $this->map($tag->refresh());
    }

    public function delete(int $id): void
    {
        PRD_Tag::query()->whereKey($id)->delete();
    }

    private function map(PRD_Tag $tag): AdminTagDTO
    {
        return new AdminTagDTO(
            id: (int) $tag->id,
            code: (string) $tag->code,
            label: (string) $tag->label,
            color: (string) ($tag->color ?? 'amber'),
            isActive: (bool) $tag->is_active,
            sortOrder: (int) $tag->sort_order,
        );
    }
}
