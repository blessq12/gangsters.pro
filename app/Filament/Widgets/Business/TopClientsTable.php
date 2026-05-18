<?php

namespace App\Filament\Widgets\Business;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Widgets\Business\Concerns\InteractsWithBusinessMetrics;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class TopClientsTable extends TableWidget
{
    use InteractsWithBusinessMetrics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Топ клиентов';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => collect($this->businessSnapshot()->topClients))
            ->columns([
                TextColumn::make('client_name')
                    ->label('Клиент')
                    ->url(fn (array $record): string => ClientResource::getUrl('edit', ['record' => $record['client_id']])),
                TextColumn::make('orders_count')
                    ->label('Заказы')
                    ->numeric(),
                TextColumn::make('revenue')
                    ->label('Выручка')
                    ->formatStateUsing(
                        fn ($state): string => Money::formatKopecksForAdmin((int) $state),
                    ),
            ])
            ->paginated(false);
    }
}
