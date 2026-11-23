<?php

namespace App\Application\Order\Checkout;

use App\Application\Order\DTO\QuoteOrderDto;

interface CheckoutQuote
{
    /**
     * @return array<string, mixed>
     */
    public function execute(QuoteOrderDto $dto): array;
}
