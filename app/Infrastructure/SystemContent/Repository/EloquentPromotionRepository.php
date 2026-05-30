<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Promotion as PromotionEntity;
use App\Domain\SystemContent\Repository\PromotionRepository;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;

final class EloquentPromotionRepository implements PromotionRepository
{
    public function findAllOrdered(): array
    {
        return SYS_Promotion::query()
            ->orderBy('id')
            ->get()
            ->map(fn (SYS_Promotion $promotion) => $this->toEntity($promotion))
            ->all();
    }

    public function findById(int $id): ?PromotionEntity
    {
        $promotion = SYS_Promotion::query()->find($id);

        return $promotion !== null ? $this->toEntity($promotion) : null;
    }

    public function save(PromotionEntity $promotion): PromotionEntity
    {
        if ($promotion->id() > 0) {
            $model = SYS_Promotion::query()->findOrFail($promotion->id());
        } else {
            $model = new SYS_Promotion();
        }

        $model->fill([
            'title' => $promotion->title(),
            'description' => $promotion->description(),
            'image' => $promotion->imagePath(),
        ]);

        $model->save();

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        SYS_Promotion::query()->whereKey($id)->delete();
    }

    private function toEntity(SYS_Promotion $promotion): PromotionEntity
    {
        return new PromotionEntity(
            id: (int) $promotion->id,
            title: $promotion->title,
            description: $promotion->description,
            imagePath: $promotion->image,
        );
    }
}
