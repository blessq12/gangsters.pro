<?php

namespace App\Application\Order\Command;

use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Repository\OrderNoteRepository;
use App\Domain\Core\Order\Repository\OrderRepository;

final class AddOrderNoteUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderNoteRepository $notes,
    ) {}

    /**
     * @return array{
     *     id: int,
     *     body: string,
     *     author_employee_id: int|null,
     *     author_name: string|null,
     *     author_role: string|null,
     *     created_at: string
     * }
     */
    public function execute(int $orderId, string $body, ?int $authorEmployeeId): array
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        if ($order->status() === OrderStatus::Delivered) {
            throw new \InvalidArgumentException('К выданному заказу нельзя добавить комментарий.');
        }

        return $this->notes->add($orderId, $body, $authorEmployeeId);
    }
}
