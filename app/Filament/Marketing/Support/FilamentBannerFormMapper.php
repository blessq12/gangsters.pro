<?php

namespace App\Filament\Marketing\Support;

use App\Application\Marketing\Banner\DTO\SaveBannerDTO;
use App\Infrastructure\SystemContent\Model\SYS_Banner;

final class FilamentBannerFormMapper
{
    public static function toFormState(SYS_Banner $banner): array
    {
        return [
            'title' => $banner->title,
            'description' => $banner->description,
            'image_mobile_upload' => self::uploadState($banner->image_mobile ?? $banner->image),
            'image_desktop_upload' => self::uploadState($banner->image_desktop ?? $banner->image),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{image_mobile: ?string, image_desktop: ?string}  $paths
     */
    public static function toSaveDto(int $id, array $data, array $paths): SaveBannerDTO
    {
        return new SaveBannerDTO(
            id: $id,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            imageMobile: $paths['image_mobile'],
            imageDesktop: $paths['image_desktop'],
        );
    }

    /**
     * @return array<int, string>|null
     */
    private static function uploadState(?string $path): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        return [$path];
    }
}
