<?php

namespace App\Application\Operations\Order\Command;

use App\Application\Order\Contracts\MarkOrderPaidContract;

final class MarkOrderPaidByIdUseCase
{
    public function __construct(
        private readonly MarkOrderPaidContract $markOrderPaid,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(string $orderId): array
    {
        return $this->markOrderPaid->execute($orderId);
    }
}
