<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SettleStopDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Delivery\Event\RouteStopSettled;
use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderAuditRepository;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Исход остановки. Заказ и платёж закрывают слушатели RouteStopSettled.
 * При delivered — overwrite payment_method + аудит до закрытия стопа.
 */
final class SettleStopUseCase
{
    private const ALLOWED_PAYMENT_METHODS = ['cash', 'card_courier', 'card_online'];

    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly OrderRepository $orders,
        private readonly OrderAuditRepository $audits,
        private readonly RouteSheetPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SettleStopDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $outcome = trim($dto->outcome);

            if ($outcome === RouteStopSettled::OUTCOME_DELIVERED) {
                $this->overwritePaymentOnDeliver($dto);
            }

            $sheet = $this->requireSheet($dto->sheetId);
            $sheet = $sheet->settle(
                $dto->orderId,
                $outcome,
                (string) ($dto->reason ?? ''),
            );

            if ($this->everyStopSettled($sheet)) {
                $sheet = $sheet->complete();
            }

            $this->sheets->save($sheet);
            $this->events->publish($sheet);

            return $this->presenter->present($sheet);
        });
    }

    private function overwritePaymentOnDeliver(SettleStopDto $dto): void
    {
        $method = trim((string) ($dto->paymentMethod ?? ''));
        if ($method === '' || ! in_array($method, self::ALLOWED_PAYMENT_METHODS, true)) {
            throw new \InvalidArgumentException('Укажи способ оплаты при выдаче.');
        }

        $order = $this->requireOrder($dto->orderId);
        $before = [
            'payment_method' => $order->paymentMethod(),
            'change_from_rubles' => $order->changeFromRubles(),
        ];

        $changeFrom = $method === 'cash' ? $order->changeFromRubles() : null;
        $order->replacePaymentTerms($method, $changeFrom);

        $after = [
            'payment_method' => $order->paymentMethod(),
            'change_from_rubles' => $order->changeFromRubles(),
        ];

        $this->orders->save($order);

        if ($before !== $after) {
            $this->audits->add(
                $order->id(),
                $dto->authorEmployeeId,
                [['field' => 'payment', 'old' => $before, 'new' => $after]],
            );
        }
    }

    private function requireSheet(int $sheetId): RouteSheet
    {
        return $this->sheets->findById($sheetId)
            ?? throw new \InvalidArgumentException('Маршрутный лист не найден.');
    }

    private function requireOrder(int $orderId): Order
    {
        return $this->orders->findById($orderId)
            ?? throw new \InvalidArgumentException('Заказ не найден.');
    }

    private function everyStopSettled(RouteSheet $sheet): bool
    {
        foreach ($sheet->stops() as $stop) {
            if ($stop->outcome() === 'pending') {
                return false;
            }
        }

        return true;
    }
}
