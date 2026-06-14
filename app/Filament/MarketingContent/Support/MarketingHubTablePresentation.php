<?php

namespace App\Filament\MarketingContent\Support;

use Filament\Tables\Columns\TextColumn;

final class MarketingHubTablePresentation
{
    public static function activeStatusColumn(): TextColumn
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
}
