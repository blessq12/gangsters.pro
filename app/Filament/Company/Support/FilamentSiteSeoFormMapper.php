<?php

namespace App\Filament\Company\Support;

use App\Application\Site\Command\UpdateAdminSiteSeoSettingsUseCase;
use App\Application\Site\DTO\UpdateSiteSeoSettingsDto;

final class FilamentSiteSeoFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        return [
            'default_title' => $detail['default_title'] ?? '',
            'default_description' => $detail['default_description'] ?? '',
            'pages' => $detail['pages'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toDto(array $data): UpdateSiteSeoSettingsDto
    {
        $pages = $data['pages'] ?? [];

        return UpdateAdminSiteSeoSettingsUseCase::pagesFromFormRows(
            is_array($pages) ? array_values($pages) : [],
        );
    }
}
