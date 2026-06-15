<?php

namespace App\Infrastructure\Shared\Snapshot;

use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Shared\ValueObject\Money;

final class CartLinesSnapshotCodec
{
    /**
     * @return array<string, mixed>
     */
    public static function serializeLine(
        int $productId,
        string $productName,
        int $quantity,
        Money $unitPrice,
        ?array $payload,
        ?string $sku = null,
    ): array {
        $line = [
            'product_id' => $productId,
            'product_name' => $productName,
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPrice->amountRubles(),
            'line_total_rubles' => $unitPrice->amountRubles() * $quantity,
            'payload' => $payload,
        ];

        if (is_string($sku) && $sku !== '') {
            $line['sku'] = $sku;
        }

        return $line;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function deserializeToCartLine(array $payload): CartLineSnapshot
    {
        return new CartLineSnapshot(
            productId: (int) ($payload['product_id'] ?? 0),
            productName: (string) ($payload['product_name'] ?? ''),
            quantity: (int) ($payload['quantity'] ?? 0),
            unitPrice: Money::rubles((int) ($payload['unit_price_rubles'] ?? 0)),
            payload: is_array($payload['payload'] ?? null) ? $payload['payload'] : null,
            sku: isset($payload['sku']) ? (string) $payload['sku'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function deserializeToOrderLine(array $payload): OrderLineSnapshot
    {
        return new OrderLineSnapshot(
            productId: (int) ($payload['product_id'] ?? 0),
            productName: (string) ($payload['product_name'] ?? ''),
            quantity: (int) ($payload['quantity'] ?? 0),
            unitPrice: Money::rubles((int) ($payload['unit_price_rubles'] ?? 0)),
            payload: is_array($payload['payload'] ?? null) ? $payload['payload'] : null,
            sku: isset($payload['sku']) ? (string) $payload['sku'] : null,
        );
    }

    /**
     * @param  iterable<CartLineSnapshot|OrderLineSnapshot>  $lines
     * @return array{lines: list<array<string, mixed>>}
     */
    public static function serializeCart(iterable $lines): array
    {
        $serializedLines = [];

        foreach ($lines as $line) {
            $serializedLines[] = self::serializeLine(
                productId: $line->productId(),
                productName: $line->productName(),
                quantity: $line->quantity(),
                unitPrice: $line->unitPrice(),
                payload: $line->payload(),
                sku: $line->sku(),
            );
        }

        return ['lines' => $serializedLines];
    }
}
