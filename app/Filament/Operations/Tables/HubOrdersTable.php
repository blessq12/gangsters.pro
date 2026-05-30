<?php

namespace App\Filament\Operations\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Query\GetAdminOrderListQuery;
use App\Filament\Operations\Resources\OrderResource;
use App\Support\Order\OrderStatusLabels;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubOrdersTable extends TableWidget
{
    protected static ?string $heading = 'Заказы';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (
                ?string $search,
                ?array $filters,
                int|string $page,
                int|string $recordsPerPage,
            ): LengthAwarePaginator {
                $status = $filters['status']['value'] ?? null;
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;

                try {
                    $result = app(GetAdminOrderListQuery::class)->execute(
                        status: filled($status) ? (string) $status : null,
                        phone: filled($search) ? $search : null,
                        page: max(1, (int) $page),
                        perPage: $perPage,
                    );
                } catch (ApiException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();

                    return new LengthAwarePaginator([], 0, $perPage, 1);
                }

                return new LengthAwarePaginator(
                    collect($result['items'])->keyBy('id'),
                    $result['total'],
                    $perPage,
                    max(1, (int) $page),
                    ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
                );
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status_label')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (array $record): string => OrderStatusLabels::statusColor((string) ($record['status'] ?? ''))),
                TextColumn::make('customer_name')
                    ->label('Клиент'),
                TextColumn::make('customer_phone')
                    ->label('Телефон'),
                TextColumn::make('total')
                    ->label('Сумма, ₽')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('payment_status')
                    ->label('Оплата')
                    ->badge()
                    ->color(fn (array $record): string => OrderStatusLabels::paymentStatusColor($record['payment_status'] ?? null)),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(OrderStatusLabels::statusOptions()),
            ])
            ->searchable()
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => OrderResource::getUrl('edit', ['record' => $record['id']])),
            ]);
    }
}
