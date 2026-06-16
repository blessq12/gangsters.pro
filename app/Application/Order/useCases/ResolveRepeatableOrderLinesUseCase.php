<?php

namespace App\Application\Order\useCases;

use App\Application\Common\Exceptions\UnauthorizedException;
use App\Application\Order\DTO\RepeatableOrderLinesDto;
use App\Domain\Order\Enum\OrderSource;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Exception\OrderRepeatNotSupportedException;
use App\Domain\Order\Port\CatalogPricingPort;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Shared\Enum\ClientKind;

/**
 * Сценарий: какие строки заказа можно повторить с актуальным каталогом.
 */
final class ResolveRepeatableOrderLinesUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CatalogPricingPort $pricing,
    ) {}

    /**
     * @return array{
     *     order_id: int,
     *     available_lines: list<array{
     *         product_id: int,
     *         quantity: int,
     *         product_name: string,
     *         unit_price_rubles: int,
     *         catalog_kind: string
     *     }>,
     *     unavailable_lines: list<array{
     *         product_id: int,
     *         quantity: int,
     *         product_name: string,
     *         reason: string
     *     }>
     * }
     */
    public function execute(RepeatableOrderLinesDto $input): array
    {
        $order = $this->orders->findById(OrderId::fromInt($input->orderId));

        if ($order === null) {
            throw OrderNotFoundException::forId($input->orderId);
        }

        $client = $order->client();

        if (
            $client->kind() !== ClientKind::Registered
            || $client->clientId() !== $input->clientId
        ) {
            throw new UnauthorizedException();
        }

        if ($order->source() !== OrderSource::Site) {
            throw OrderRepeatNotSupportedException::forNonSiteOrder();
        }

        /** @var array<int, int> $quantityByProductId */
        $quantityByProductId = [];

        foreach ($order->cart()->lines() as $line) {
            if ($line->isPromotionBenefitLine()) {
                continue;
            }

            $productId = $line->productId();
            $quantityByProductId[$productId] = ($quantityByProductId[$productId] ?? 0) + $line->quantity();
        }

        $availableLines = [];
        $unavailableLines = [];

        foreach ($quantityByProductId as $productId => $quantity) {
            $quote = $this->pricing->findActiveProductQuote($productId);
            $productName = $this->resolveUserLineName($order->cart()->lines(), $productId);

            if ($quote === null) {
                $unavailableLines[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'product_name' => $productName,
                    'reason' => 'inactive',
                ];

                continue;
            }

            $availableLines[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'product_name' => $quote->productName(),
                'unit_price_rubles' => $quote->unitPrice()->amountRubles(),
                'catalog_kind' => $quote->catalogKind(),
            ];
        }

        return [
            'order_id' => $input->orderId,
            'available_lines' => $availableLines,
            'unavailable_lines' => $unavailableLines,
        ];
    }

    /**
     * @param  list<OrderLineSnapshot>  $lines
     */
    private function resolveUserLineName(array $lines, int $productId): string
    {
        foreach ($lines as $line) {
            if ($line->productId() === $productId && ! $line->isPromotionBenefitLine()) {
                return $line->productName();
            }
        }

        return 'Товар';
    }
}
