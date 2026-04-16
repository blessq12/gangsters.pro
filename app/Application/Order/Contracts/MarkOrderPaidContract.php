<?php

namespace App\Application\Order\Contracts;

interface MarkOrderPaidContract
{
    /**
     * @return array<string, mixed>
     */
    public function execute(string $orderId): array;
}
