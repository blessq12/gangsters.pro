<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SettleStopDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Delivery\Event\RouteStopSettled;
use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Исход остановки. Order и Payment закрывают слушатели RouteStopSettled.
 */
final class SettleStopUseCase
{
    private const ALLOWED_PAYMENT_METHODS = ['cash', 'card_courier', 'card_online'];

    public function __construct(
        private readonly RouteSheetRepository $sheets,
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
            $paymentMethod = $this->normalizePaymentMethod($outcome, $dto->paymentMethod);

            $sheet = $this->requireSheet($dto->sheetId);
            $sheet = $sheet->settle(
                $dto->orderId,
                $outcome,
                (string) ($dto->reason ?? ''),
                $paymentMethod,
                $dto->authorEmployeeId,
            );

            if ($this->everyStopSettled($sheet)) {
                $sheet = $sheet->complete();
            }

            $this->sheets->save($sheet);
            $this->events->publish($sheet);

            return $this->presenter->present($sheet);
        });
    }

    private function normalizePaymentMethod(string $outcome, ?string $paymentMethod): ?string
    {
        if ($outcome !== RouteStopSettled::OUTCOME_DELIVERED) {
            return null;
        }

        $method = trim((string) $paymentMethod);
        if ($method === '' || ! in_array($method, self::ALLOWED_PAYMENT_METHODS, true)) {
            throw new \InvalidArgumentException('Укажи способ оплаты при выдаче.');
        }

        return $method;
    }

    private function requireSheet(int $sheetId): RouteSheet
    {
        return $this->sheets->findById($sheetId)
            ?? throw new \InvalidArgumentException('Маршрутный лист не найден.');
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
