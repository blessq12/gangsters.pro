<?php

namespace App\Filament\Operations\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Shopping\Query\GetAdminShoppingSessionDetailQuery;
use App\Application\Operations\Shopping\Query\GetAdminShoppingSessionListQuery;
use App\Filament\Operations\Concerns\ConfiguresHubTablePagination;
use App\Filament\Operations\Resources\ClientResource;
use App\Filament\Operations\Schemas\ActiveCartDetailSchema;
use App\Filament\Operations\Support\FilamentActiveCartDetailMapper;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubActiveCartsTable extends TableWidget
{
    use ConfiguresHubTablePagination;

    protected static ?string $heading = 'Активные корзины';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $table = $table
            ->records(function (
                ?string $search,
                ?array $filters,
                int|string $page,
                int|string $recordsPerPage,
            ): LengthAwarePaginator {
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;

                try {
                    $result = app(GetAdminShoppingSessionListQuery::class)->execute(
                        search: filled($search) ? $search : null,
                        page: max(1, (int) $page),
                        perPage: $perPage,
                    );
                } catch (ApiException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();

                    return $this->buildEmptyHubLengthAwarePaginator($perPage);
                }

                return $this->buildHubLengthAwarePaginator(
                    $result,
                    max(1, (int) $page),
                    $perPage,
                );
            })
            ->emptyStateHeading('Нет активных корзин')
            ->emptyStateDescription('Здесь отображаются неистёкшие shopping-сессии с непустой корзиной.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingCart)
            ->searchPlaceholder('ID сессии / client_id / public_id / order_id')
            ->searchable()
            ->columns([
                TextColumn::make('client_label')
                    ->label('Клиент')
                    ->badge()
                    ->color(fn (array $record): string => (string) ($record['client_badge_color'] ?? 'gray'))
                    ->url(fn (array $record): ?string => filled($record['client_id'] ?? null)
                        ? ClientResource::getUrl('edit', ['record' => (int) $record['client_id']])
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('cart_lines_count')
                    ->label('Позиций в корзине')
                    ->numeric(),
                TextColumn::make('favorites_count')
                    ->label('Избранное')
                    ->numeric(),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('expires_at')
                    ->label('Истекает')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть')
                    ->modalHeading(fn (array $record): string => 'Корзина: '.($record['client_label'] ?? '—'))
                    ->modalDescription(fn (array $record): string => sprintf(
                        '%d поз. · обновлено %s',
                        (int) ($record['cart_lines_count'] ?? 0),
                        filled($record['updated_at'] ?? null)
                            ? date('d.m.Y H:i', strtotime((string) $record['updated_at']))
                            : '—',
                    ))
                    ->modalWidth(Width::ThreeExtraLarge)
                    ->schema(ActiveCartDetailSchema::components())
                    ->fillForm(function (array $record): array {
                        try {
                            $snapshot = app(GetAdminShoppingSessionDetailQuery::class)->execute((int) $record['id']);

                            return FilamentActiveCartDetailMapper::toFormState($snapshot);
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();

                            return FilamentActiveCartDetailMapper::loadError($exception->getMessage());
                        }
                    }),
            ]);

        return $this->configureHubPagination($table, 'activeCarts');
    }
}
