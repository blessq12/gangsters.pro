<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Banner as BannerEntity;
use App\Domain\SystemContent\Repository\BannerRepository;
use App\Infrastructure\SystemContent\Model\SYS_Banner;

final class EloquentBannerRepository implements BannerRepository
{
    public function findAllOrdered(): array
    {
        return SYS_Banner::query()
            ->orderBy('id')
            ->get()
            ->map(fn (SYS_Banner $banner) => $this->toEntity($banner))
            ->all();
    }

    public function findById(int $id): ?BannerEntity
    {
        $banner = SYS_Banner::query()->find($id);

        return $banner !== null ? $this->toEntity($banner) : null;
    }

    public function save(BannerEntity $banner): BannerEntity
    {
        if ($banner->id() > 0) {
            $model = SYS_Banner::query()->findOrFail($banner->id());
        } else {
            $model = new SYS_Banner();
        }

        $model->fill([
            'title' => $banner->title(),
            'description' => $banner->description(),
            'image' => $banner->imagePath(),
            'image_mobile' => $banner->imageMobilePath(),
            'image_desktop' => $banner->imageDesktopPath(),
        ]);

        $model->save();

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        SYS_Banner::query()->whereKey($id)->delete();
    }

    private function toEntity(SYS_Banner $banner): BannerEntity
    {
        return new BannerEntity(
            id: (int) $banner->id,
            title: $banner->title,
            description: $banner->description,
            imagePath: $banner->image,
            imageMobilePath: $banner->image_mobile ?? null,
            imageDesktopPath: $banner->image_desktop ?? null,
        );
    }
}
