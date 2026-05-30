<?php

namespace App\Filament\Marketing\Support;

use App\Application\Marketing\Banner\DTO\SaveBannerDTO;

final class FilamentBannerFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        return [
            'title' => $detail['title'] ?? null,
            'description' => $detail['description'] ?? null,
            'image_mobile_upload' => self::uploadState($detail['image_mobile'] ?? $detail['image'] ?? null),
            'image_desktop_upload' => self::uploadState($detail['image_desktop'] ?? $detail['image'] ?? null),
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
