<?php

namespace App\Application\Order\DTO;

/**
 * Частичная правка заказа администратором. Null = поле не трогаем.
 */
final readonly class UpdateAdminOrderDto
{
    /**
     * @param  array{name?: string, phone?: string, id?: int|null}|null  $client
     * @param  array{
     *     method?: string,
     *     zone_id?: string|null,
     *     fee_kopecks?: int,
     *     address?: array{
     *         street?: string,
     *         house?: string,
     *         entrance?: string|null,
     *         floor?: string|null,
     *         apartment?: string|null,
     *         comment?: string|null,
     *         lat?: float|null,
     *         lng?: float|null
     *     }|null
     * }|null  $delivery
     * @param  list<array{
     *     product_id: int,
     *     name: string,
     *     unit_price_kopecks: int,
     *     quantity: int,
     *     line_type: string
     * }>|null  $lines
     */
    public function __construct(
        public int $orderId,
        public ?int $authorEmployeeId,
        public ?array $client = null,
        public ?array $delivery = null,
        public ?string $paymentMethod = null,
        public ?int $changeFromRubles = null,
        public bool $changeFromRublesProvided = false,
        public ?array $lines = null,
        public ?string $scheduledAt = null,
        public bool $scheduledAtProvided = false,
    ) {}
}
