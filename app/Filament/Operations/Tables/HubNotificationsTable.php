<?php

namespace App\Filament\Operations\Tables;

use App\Filament\Support\AdminNotificationDeliveryTableQuery;
use App\Filament\Operations\Concerns\ConfiguresHubTablePagination;
use App\Support\Notifications\NotificationDeliveryLabels;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubNotificationsTable extends TableWidget
{
    use ConfiguresHubTablePagination;

    protected static ?string $heading = 'Уведомления';

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
                $channel = $filters['channel']['value'] ?? null;
                $status = $filters['status']['value'] ?? null;
                $dateFrom = $filters['period']['dateFrom'] ?? null;
                $dateTo = $filters['period']['dateTo'] ?? null;
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;

                return app(AdminNotificationDeliveryTableQuery::class)->paginate(
                    channel: filled($channel) ? (string) $channel : null,
                    status: filled($status) ? (string) $status : null,
                    dateFrom: filled($dateFrom) ? (string) $dateFrom : null,
                    dateTo: filled($dateTo) ? (string) $dateTo : null,
                    search: filled($search) ? $search : null,
                    page: max(1, (int) $page),
                    perPage: $perPage,
                    pageName: $this->getTablePaginationPageName(),
                )->onEachSide(0);
            })
            ->emptyStateHeading('Нет записей')
            ->emptyStateDescription('Здесь отображается журнал исходящих клиентских уведомлений.')
            ->emptyStateIcon(Heroicon::OutlinedBell)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('channel_label')
                    ->label('Канал')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('event_type_label')
                    ->label('Событие'),
                TextColumn::make('recipient')
                    ->label('Получатель')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status_label')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (array $record): string => NotificationDeliveryLabels::statusColor($record['status'] ?? null)),
                TextColumn::make('error_message')
                    ->label('Ошибка')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Канал')
                    ->options(NotificationDeliveryLabels::channelOptions()),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(NotificationDeliveryLabels::statusOptions()),
                Filter::make('period')
                    ->label('Период')
                    ->schema([
                        DatePicker::make('dateFrom')->label('С'),
                        DatePicker::make('dateTo')->label('По'),
                    ]),
            ])
            ->searchPlaceholder('Email / событие / ошибка')
            ->searchable()
            ->recordActions([
                ViewAction::make()
                    ->label('Детали')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть')
                    ->modalHeading(fn (array $record): string => sprintf(
                        '%s · %s',
                        $record['event_type_label'] ?? '—',
                        $record['recipient'] ?? '—',
                    ))
                    ->schema([
                        TextInput::make('channel_label')
                            ->label('Канал')
                            ->disabled(),
                        TextInput::make('event_type_label')
                            ->label('Событие')
                            ->disabled(),
                        TextInput::make('recipient')
                            ->label('Получатель')
                            ->disabled(),
                        TextInput::make('status_label')
                            ->label('Статус')
                            ->disabled(),
                        TextInput::make('created_at')
                            ->label('Дата')
                            ->disabled(),
                        Textarea::make('error_message')
                            ->label('Ошибка')
                            ->disabled()
                            ->rows(3)
                            ->visible(fn (array $record): bool => filled($record['error_message'] ?? null)),
                        Textarea::make('payload_json')
                            ->label('Payload')
                            ->disabled()
                            ->rows(6)
                            ->visible(fn (array $record): bool => filled($record['payload_json'] ?? null)),
                    ]),
            ]);

        return $this->configureHubPagination($table, 'notifications');
    }
}
