<?php

namespace App\Application\Order\DTO;

final readonly class CreateOrderDto
{
    /**
     * @param  string|null  $rulesVersion  версия контекста, по которой фронт считал корзину
     * @param  int|null  $expectedTotalKopecks  итог, который видел клиент
     */
    public function __construct(
        public string $clientRequestId,
        public QuoteOrderDto $checkout,
        public ?string $rulesVersion = null,
        public ?int $expectedTotalKopecks = null,
    ) {}
}
