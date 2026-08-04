<?php

namespace App\Application\Crm\Query;

use App\Domain\Crm\Port\CrmCatalogAvailabilityPort;
use App\Domain\Crm\Port\CrmClientOrdersPort;
use Illuminate\Auth\AuthenticationException;

final class GetRepeatableOrderLinesUseCase
{
    public function __construct(
        private readonly CrmClientOrdersPort $orders,
        private readonly CrmCatalogAvailabilityPort $catalogAvailability,
    ) {}

    /**
     * @return array{available_lines: list<array<string, mixed>>, unavailable_lines: list<array<string, mixed>>}
     */
    public function execute(int $clientId, int $orderId): array
    {
        $order = $this->orders->findByIdForClient($orderId, $clientId);
        if ($order === null) {
            throw new AuthenticationException();
        }

        $items = [];
        foreach ($order['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $kind = (string) ($line['kind'] ?? 'user');
            if ($kind === 'gift' || $kind === 'complement') {
                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            $productName = (string) ($line['product_name'] ?? '');

            $items[] = [
                'product_id' => $productId,
                'product_name' => $productName !== '' ? $productName : ('Товар #'.$productId),
                'quantity' => max(1, (int) ($line['quantity'] ?? 1)),
                'unit_price_rubles' => (int) ($line['unit_price_rubles'] ?? 0),
            ];
        }

        $productIds = array_values(array_unique(array_filter(
            array_map(static fn (array $item): int => $item['product_id'], $items),
            static fn (int $id): bool => $id > 0,
        )));

        $kinds = $this->catalogAvailability->activeCatalogKindsByIds($productIds);

        $available = [];
        $unavailable = [];

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $catalogKind = $kinds[$productId] ?? null;
            $line = [
                ...$item,
                'catalog_kind' => $catalogKind ?? 'product',
            ];

            if ($productId > 0 && $catalogKind !== null) {
                $available[] = $line;
            } else {
                $unavailable[] = $line;
            }
        }

        return [
            'available_lines' => $available,
            'unavailable_lines' => $unavailable,
        ];
    }
}
