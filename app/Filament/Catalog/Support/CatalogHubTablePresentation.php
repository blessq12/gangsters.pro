<?php

namespace App\Filament\Catalog\Support;

use App\Domain\Catalog\Enum\ProductStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class CatalogHubTablePresentation
{
    public static function categoryStatusColumn(): TextColumn
    {
        return TextColumn::make('is_active')
            ->label('Статус')
            ->badge()
            ->formatStateUsing(
                fn (bool $state): string => $state ? 'Активна' : 'Неактивна',
            )
            ->color(
                fn (bool $state): string => $state ? 'success' : 'gray',
            );
    }

    public static function tagStatusColumn(): TextColumn
    {
        return TextColumn::make('is_active')
            ->label('Статус')
            ->badge()
            ->formatStateUsing(
                fn (bool $state): string => $state ? 'Активен' : 'Неактивен',
            )
            ->color(
                fn (bool $state): string => $state ? 'success' : 'gray',
            );
    }

    public static function catalogItemStatusColumn(): TextColumn
    {
        return TextColumn::make('status')
            ->label('Статус')
            ->badge()
            ->formatStateUsing(
                fn (string $state): string => match ($state) {
                    ProductStatus::Active->value => 'Активен',
                    ProductStatus::Archived->value => 'В архиве',
                    default => $state,
                },
            )
            ->color(
                fn (string $state): string => match ($state) {
                    ProductStatus::Active->value => 'success',
                    ProductStatus::Archived->value => 'gray',
                    default => 'gray',
                },
            );
    }

    public static function categoryStatusFilter(): SelectFilter
    {
        return SelectFilter::make('is_active')
            ->label('Статус')
            ->options([
                '1' => 'Активна',
                '0' => 'Неактивна',
            ]);
    }

    public static function catalogItemStatusFilter(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Статус')
            ->options([
                ProductStatus::Active->value => 'Активен',
                ProductStatus::Archived->value => 'В архиве',
            ]);
    }

    public static function productMetaFilter(): SelectFilter
    {
        return SelectFilter::make('product_meta')
            ->label('Мета товара')
            ->options([
                'meta_counts_as_roll' => 'Считается как ролл',
                'meta_gift_candidate' => 'Кандидат на подарок',
                'meta_is_complement_set' => 'Набор дополнений',
            ])
            ->query(function (Builder $query, array $data): void {
                $column = $data['value'] ?? null;

                if (! is_string($column)) {
                    return;
                }

                if (! in_array($column, [
                    'meta_counts_as_roll',
                    'meta_gift_candidate',
                    'meta_is_complement_set',
                ], true)) {
                    return;
                }

                $query->where($column, true);
            });
    }
}
