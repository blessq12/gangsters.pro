<?php

namespace App\Filament\Operations\Resources\OrderResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Command\CancelOrderByIdUseCase;
use App\Application\Operations\Order\Command\ChangeOrderStatusUseCase;
use App\Application\Operations\Order\Command\MarkOrderPaidByIdUseCase;
use App\Application\Operations\Order\Command\UpdateAdminOrderUseCase;
use App\Application\Operations\Order\DTO\ChangeOrderStatusDTO;
use App\Application\Operations\Order\DTO\UpdateAdminOrderDto;
use App\Domain\Order\Enums\PaymentStatus;
use App\Filament\Operations\Resources\OrderResource;
use App\Filament\Operations\Support\FilamentOrderFormMapper;
use App\Filament\Operations\Support\ResolvesOperationsEditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    use ResolvesOperationsEditRecord;

    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Заказ';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Infrastructure\Order\Model\ORD_Order $record */
        $record = $this->getRecord()->load('items');

        return FilamentOrderFormMapper::toFormState($record);
    }

    protected function getHeaderActions(): array
    {
        $orderId = (string) $this->getRecord()->getKey();
        $status = (string) $this->getRecord()->status;
        $paymentStatus = (string) $this->getRecord()->payment_status;

        return [
            Action::make('preparing')
                ->label('В работу')
                ->color('warning')
                ->visible($status === 'new')
                ->action(fn () => $this->changeStatus($orderId, 'preparing')),
            Action::make('in_transit')
                ->label('В пути')
                ->color('primary')
                ->visible($status === 'preparing')
                ->action(fn () => $this->changeStatus($orderId, 'in_transit')),
            Action::make('delivered')
                ->label('Доставлен')
                ->color('success')
                ->visible(in_array($status, ['preparing', 'in_transit'], true))
                ->action(fn () => $this->changeStatus($orderId, 'delivered')),
            Action::make('mark_paid')
                ->label('Оплачен')
                ->color('success')
                ->visible($paymentStatus !== PaymentStatus::Paid->value)
                ->action(fn () => $this->markPaid($orderId)),
            Action::make('cancel')
                ->label('Удалить заказ')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Удалить заказ безвозвратно?')
                ->modalDescription('Заказ будет удалён из базы (hard delete).')
                ->action(fn () => $this->cancelOrder($orderId)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            app(UpdateAdminOrderUseCase::class)->execute(new UpdateAdminOrderDto(
                orderId: (string) $record->getKey(),
                items: FilamentOrderFormMapper::toOrderItems($data),
            ));
            Notification::make()->title('Состав заказа сохранён')->success()->send();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    private function changeStatus(string $orderId, string $status): void
    {
        try {
            app(ChangeOrderStatusUseCase::class)->execute(new ChangeOrderStatusDTO($orderId, $status));
            Notification::make()->title('Статус обновлён')->success()->send();
            $this->refreshRecordFormState();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function markPaid(string $orderId): void
    {
        try {
            app(MarkOrderPaidByIdUseCase::class)->execute($orderId);
            Notification::make()->title('Заказ отмечен оплаченным')->success()->send();
            $this->refreshRecordFormState();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function cancelOrder(string $orderId): void
    {
        try {
            app(CancelOrderByIdUseCase::class)->execute($orderId);
            Notification::make()->title('Заказ удалён')->success()->send();
            $this->redirect(OrderResource::getIndexUrl());
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }
    }
}
