<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Shared\Transaction\TransactionManager;
use DateTimeImmutable;

/**
 * В момент scheduled_at: preorder → confirmed (+ тикет через OrderConfirmed).
 */
final class ActivateDuePreordersUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array{activated: int, order_ids: list<int>}
     */
    public function execute(?DateTimeImmutable $at = null): array
    {
        $now = $at ?? new DateTimeImmutable();
        $due = $this->orders->listDuePreorders($now);
        $activated = [];

        foreach ($due as $order) {
            if ($order->status() !== OrderStatus::Preorder) {
                continue;
            }

            $this->transaction->run(function () use ($order, &$activated): void {
                $fresh = $this->orders->findById($order->id());
                if ($fresh === null || $fresh->status() !== OrderStatus::Preorder) {
                    return;
                }

                if ($fresh->branchId() === null || $fresh->branchId() < 1) {
                    return;
                }

                if ($fresh->paymentMethod() === null || trim((string) $fresh->paymentMethod()) === '') {
                    return;
                }

                $fresh->accept();
                $fresh->confirm();
                $this->orders->save($fresh);
                $this->events->publish($fresh);
                $activated[] = $fresh->id();
            });
        }

        return [
            'activated' => count($activated),
            'order_ids' => $activated,
        ];
    }
}
