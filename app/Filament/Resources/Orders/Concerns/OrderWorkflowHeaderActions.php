<?php

namespace App\Filament\Resources\Orders\Concerns;

use App\Application\Order\Contracts\CancelOrderContract;
use App\Application\Order\Contracts\MarkOrderPaidContract;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Support\Order\OrderStatusLabels;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

trait OrderWorkflowHeaderActions
{
    /**
     * @return array<int, Action|DeleteAction>
     */
    protected function getOrderWorkflowHeaderActions(): array
    {
        return [
            Action::make('markPreparing')
                ->label('В готовку')
                ->icon(Heroicon::OutlinedFire)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->status === 'new')
                ->action(fn () => $this->updateOrderStatus('preparing')),
            Action::make('markInTransit')
                ->label('В пути')
                ->icon(Heroicon::OutlinedTruck)
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->status === 'preparing')
                ->action(fn () => $this->updateOrderStatus('in_transit')),
            Action::make('markDelivered')
                ->label('Доставлен')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->status === 'in_transit')
                ->action(fn () => $this->updateOrderStatus('delivered')),
            Action::make('markPaid')
                ->label('Оплачен')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->requiresConfirmation()
                ->visible(
                    fn (): bool => $this->getRecord()->payment_status !== PaymentStatus::Paid->value,
                )
                ->action(function (): void {
                    app(MarkOrderPaidContract::class)->execute($this->getRecord()->id);
                    $this->refreshRecord();
                    Notification::make()
                        ->title('Заказ отмечен как оплаченный')
                        ->success()
                        ->send();
                }),
            DeleteAction::make()
                ->label('Удалить заказ')
                ->requiresConfirmation()
                ->action(function (): void {
                    $order = app(OrderRepositoryInterface::class)->getById($this->getRecord()->id);
                    app(CancelOrderContract::class)->cancel($order);
                    $this->redirect($this->getResource()::getUrl('index', ['tab' => 'new']));
                }),
        ];
    }

    private function updateOrderStatus(string $status): void
    {
        /** @var ORD_Order $record */
        $record = $this->getRecord();
        $record->update(['status' => $status]);
        $this->refreshRecord();

        Notification::make()
            ->title('Статус: '.OrderStatusLabels::statusLabel($status))
            ->body('Заказ отображается на вкладке «'.OrderStatusLabels::statusTabLabel($status).'».')
            ->success()
            ->send();
    }
}
