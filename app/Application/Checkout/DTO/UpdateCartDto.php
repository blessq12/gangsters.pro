<?php

namespace App\Application\Checkout\DTO;

/**
 * Вход сценария: обновить корзину (одна позиция за вызов).
 *
 * quantity > 0 — добавить товар или изменить его количество.
 * quantity = 0 — удалить товар из корзины.
 */
final readonly class UpdateCartDto
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        public int $productId,
        public int $quantity,
        public ?array $payload = null,
    ) {}
}
