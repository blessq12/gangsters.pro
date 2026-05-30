<?php

namespace App\Filament\Operations\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Command\ChangeOrderStatusUseCase;
use App\Application\Operations\Order\Command\MarkOrderPaidByIdUseCase;
use App\Application\Operations\Order\DTO\ChangeOrderStatusDTO;
use App\Application\Operations\Order\Query\GetAdminOrderListQuery;
use App\Domain\Order\Enums\PaymentStatus;
use App\Filament\Operations\Concerns\ConfiguresHubTablePagination;
use App\Filament\Operations\Resources\OrderResource;
use App\Support\Order\OrderStatusLabels;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HubOrdersTable extends TableWidget
{
    use ConfiguresHubTablePagination;

    protected static ?string $heading = 'Заказы';

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
                $status = $filters['status']['value'] ?? null;
                $paymentStatus = $filters['payment_status']['value'] ?? null;
                $dateFrom = $filters['period']['dateFrom'] ?? null;
                $dateTo = $filters['period']['dateTo'] ?? null;
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;

                try {
                    $result = app(GetAdminOrderListQuery::class)->execute(
                        status: filled($status) ? (string) $status : null,
                        dateFrom: filled($dateFrom) ? (string) $dateFrom : null,
                        dateTo: filled($dateTo) ? (string) $dateTo : null,
                        search: filled($search) ? $search : null,
                        paymentStatus: filled($paymentStatus) ? (string) $paymentStatus : null,
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
                SelectFilter::make('payment_status')
                    ->label('Оплата')
                    ->options(PaymentStatus::options()),
                Filter::make('period')
                    ->label('Период')
                    ->schema([
                        DatePicker::make('dateFrom')->label('С'),
                        DatePicker::make('dateTo')->label('По'),
                    ]),
            ])
            ->searchPlaceholder('ID / телефон / имя')
            ->searchable()
            ->headerActions([
                CreateAction::make()
                    ->label('Создать заказ')
                    ->url(OrderResource::getUrl('create')),
            ])
            ->selectable()
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => OrderResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('mark_paid')
                    ->label('Оплачен')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (array $record): bool => ($record['payment_status'] ?? null) !== PaymentStatus::Paid->value)
                    ->action(function (array $record): void {
                        $this->runMarkPaid((string) $record['id']);
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('change_status')
                    ->label('Сменить статус')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->form([
                        Select::make('status')
                            ->label('Статус')
                            ->options(OrderStatusLabels::statusOptions())
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $this->runBulkStatusChange($records, (string) $data['status']);
                    }),
                BulkAction::make('mark_paid')
                    ->label('Отметить оплаченными')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $this->runBulkMarkPaid($records);
                    }),
            ]);

        return $this->configureHubPagination($table, 'orders');
    }

    private function runMarkPaid(string $orderId): void
    {
        try {
            app(MarkOrderPaidByIdUseCase::class)->execute($orderId);
            Notification::make()->title('Заказ отмечен оплаченным')->success()->send();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function runBulkStatusChange(Collection $records, string $status): void
    {
        $errors = [];
        $success = 0;

        foreach ($records as $record) {
            $orderId = (string) (is_array($record) ? ($record['id'] ?? '') : $record->getKey());

            try {
                app(ChangeOrderStatusUseCase::class)->execute(new ChangeOrderStatusDTO($orderId, $status));
                $success++;
            } catch (ApiException $exception) {
                $errors[] = $orderId.': '.$exception->getMessage();
            }
        }

        $this->notifyBulkResult($success, $errors, 'Статус обновлён');
    }

    private function runBulkMarkPaid(Collection $records): void
    {
        $errors = [];
        $success = 0;
        $skipped = 0;

        foreach ($records as $record) {
            $orderId = (string) (is_array($record) ? ($record['id'] ?? '') : $record->getKey());
            $paymentStatus = is_array($record) ? ($record['payment_status'] ?? null) : null;

            if ($paymentStatus === PaymentStatus::Paid->value) {
                $skipped++;

                continue;
            }

            try {
                app(MarkOrderPaidByIdUseCase::class)->execute($orderId);
                $success++;
            } catch (ApiException $exception) {
                $errors[] = $orderId.': '.$exception->getMessage();
            }
        }

        $message = "Оплачено: {$success}";
        if ($skipped > 0) {
            $message .= ", пропущено: {$skipped}";
        }

        $this->notifyBulkResult($success, $errors, $message);
    }

    /**
     * @param  list<string>  $errors
     */
    private function notifyBulkResult(int $success, array $errors, string $successTitle): void
    {
        if ($success > 0 && $errors === []) {
            Notification::make()->title($successTitle)->success()->send();

            return;
        }

        if ($success > 0) {
            Notification::make()
                ->title($successTitle)
                ->body(implode("\n", $errors))
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Не удалось выполнить операцию')
            ->body(implode("\n", $errors))
            ->danger()
            ->send();
    }
}
