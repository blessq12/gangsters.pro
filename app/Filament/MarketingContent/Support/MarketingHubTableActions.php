<?php

namespace App\Filament\MarketingContent\Support;

use App\Filament\MarketingContent\Resources\BannerResource;
use App\Filament\MarketingContent\Resources\PromotionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class MarketingHubTableActions
{
    /**
     * @param  class-string  $resourceClass
     */
    public static function apply(Table $table, string $resourceClass): Table
    {
        return $table
            ->modelLabel($resourceClass::getModelLabel())
            ->pluralModelLabel($resourceClass::getPluralModelLabel())
            ->emptyStateHeading(self::emptyStateHeading($resourceClass))
            ->emptyStateDescription(self::emptyStateDescription($resourceClass))
            ->headerActions([
                Action::make('create')
                    ->label('Создать')
                    ->icon(Heroicon::Plus)
                    ->url(fn (): string => $resourceClass::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Model $record): string => $resourceClass::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }

    public static function forBanners(Table $table): Table
    {
        return self::apply($table, BannerResource::class);
    }

    public static function forPromotions(Table $table): Table
    {
        return self::apply($table, PromotionResource::class);
    }

    /**
     * @param  class-string  $resourceClass
     */
    private static function emptyStateHeading(string $resourceClass): string
    {
        return match ($resourceClass) {
            BannerResource::class => 'Баннеры не найдены',
            PromotionResource::class => 'Акции не найдены',
            default => 'Записи не найдены',
        };
    }

    /**
     * @param  class-string  $resourceClass
     */
    private static function emptyStateDescription(string $resourceClass): string
    {
        return match ($resourceClass) {
            BannerResource::class => 'Добавьте первый баннер для главной страницы.',
            PromotionResource::class => 'Добавьте первую акцию для блока на главной.',
            default => 'Создайте первую запись.',
        };
    }
}
