<?php

namespace App\Support\Product;

use App\Domain\Product\Entity\Product;

final class ProductStatusLabels
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            Product::STATUS_ACTIVE => 'На витрине (активен)',
            Product::STATUS_ARCHIVED => 'В архиве',
        ];
    }

    public static function label(string $status): string
    {
        return match ($status) {
            Product::STATUS_ACTIVE => 'Активен',
            Product::STATUS_ARCHIVED => 'Архив',
            default => $status,
        };
    }

    public static function color(string $status): string
    {
        return match ($status) {
            Product::STATUS_ACTIVE => 'success',
            Product::STATUS_ARCHIVED => 'gray',
            default => 'gray',
        };
    }
}
