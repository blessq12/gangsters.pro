<?php

namespace App\Application\Order\Command;

use App\Domain\Core\Order\Repository\OrderNoteRepository;
use App\Domain\Core\Order\Repository\OrderRepository;

final class AddOrderNoteUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderNoteRepository $notes,
    ) {}

    /**
     * @return array{id: int, body: string, author_employee_id: int|null, created_at: string}
     */
    public function execute(int $orderId, string $body, ?int $authorEmployeeId): array
    {
        if ($this->orders->findById($orderId) === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        return $this->notes->add($orderId, $body, $authorEmployeeId);
    }
}
