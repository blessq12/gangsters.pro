<?php

namespace App\Domain\Order\Port;

/**
 * Чтение акционных данных при оформлении заказа: только массивы, без сущностей Promotion.
 *
 * Правило подарка: {order_channel: string, min_order_amount_kopecks: int, benefit_type: string,
 *                   is_active: bool, allowed_weekdays: list<int>}
 * Роль товара: {counts_as_roll: bool, complement_set: bool, paid_twin_product_id: ?int}
 * Результат расчёта доставки: {fee_kopecks: int, free_delivery_threshold_kopecks: ?int, zone_id: ?string}
 */
interface OrderPromotionPort
{
    /**
     * Правило подарка для канала заказа (pickup / courier) или null.
     *
     * @return array{
     *     order_channel: string,
     *     min_order_amount_kopecks: int,
     *     benefit_type: string,
     *     is_active: bool,
     *     allowed_weekdays: list<int>
     * }|null
     */
    public function findGiftRuleForChannel(string $channel): ?array;

    /**
     * Доступно ли правило подарка в указанный момент (с учётом дней недели).
     *
     * @param  array<string, mixed>  $rule
     */
    public function isGiftRuleAllowedOnDate(array $rule, \DateTimeInterface $at, string $timezone): bool;

    /**
     * @param  list<int>  $productIds
     * @return array<int, array{counts_as_roll: bool, complement_set: bool, paid_twin_product_id: ?int}>
     */
    public function findRolesByProductIds(array $productIds): array;

    /**
     * @return list<int>
     */
    public function findActiveGiftCandidateProductIds(): array;

    /**
     * @return list<int>
     */
    public function findActiveComplementSetProductIds(): array;

    /**
     * @return array{rolls_per_set: int, max_sets: int, is_active: bool}
     */
    public function complementSetBenefit(): array;

    /**
     * Стоимость доставки с учётом акции.
     *
     * @return array{fee_kopecks: int, free_delivery_threshold_kopecks: ?int, zone_id: ?string}
     */
    public function resolveDeliveryFee(
        ?string $deliveryMethod,
        int $currentKopecks,
        ?float $latitude,
        ?float $longitude,
    ): array;
}
