<?php

namespace App\Filament\Catalog\Support;

use App\Domain\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\PRD_Product;

final class FilamentProductPersistence
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        $status = $data['status'] ?? ProductStatus::Active->value;

        $data['archived_at'] = $status === ProductStatus::Archived->value
            ? ($data['archived_at'] ?? now())
            : null;

        if (array_key_exists('ingredients', $data) && is_array($data['ingredients'])) {
            $data['ingredients'] = array_values(array_filter(array_map(
                static fn (mixed $value): string => trim((string) $value),
                $data['ingredients'],
            ), static fn (string $value): bool => $value !== ''));
        }

        return $data;
    }

    public static function ensureProductKind(PRD_Product $record): void
    {
        if ($record->catalog_kind !== 'product') {
            abort(404);
        }
    }

    public static function ensureSetKind(PRD_Product $record): void
    {
        if ($record->catalog_kind !== 'set') {
            abort(404);
        }
    }
}
