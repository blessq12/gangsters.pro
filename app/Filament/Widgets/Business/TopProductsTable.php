<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class TopProductsTable extends TableWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Топ товаров';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => collect($this->businessSnapshot()->topProducts))
            ->columns([
                TextColumn::make('product_name')
                    ->label('Товар')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->numeric(),
                TextColumn::make('revenue')
                    ->label('Выручка')
                    ->formatStateUsing(
                        fn ($state): string => Money::formatKopecksForAdmin((int) $state),
                    ),
                TextColumn::make('product_original_id')
                    ->label('')
                    ->formatStateUsing(function ($state): ?string {
                        if ($state === null) {
                            return null;
                        }

                        return 'Открыть';
                    })
                    ->url(
                        fn (array $record): ?string => $record['product_original_id'] !== null
                            ? ProductResource::getUrl('edit', ['record' => $record['product_original_id']])
                            : null,
                        shouldOpenInNewTab: true,
                    )
                    ->alignEnd(),
            ])
            ->paginated(false);
    }
}
