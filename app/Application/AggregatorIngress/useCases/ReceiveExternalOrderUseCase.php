<?php

namespace App\Application\AggregatorIngress\useCases;

use App\Application\AggregatorIngress\DTO\ReceiveExternalOrderDto;
use App\Application\AggregatorIngress\Mapper\IngressMappedOrderToCreateOrderMapper;
use App\Application\AggregatorIngress\Port\IngressPartnerAdapter;
use App\Application\AggregatorIngress\Port\IngressPartnerAuthenticator;
use App\Application\AggregatorIngress\Services\IngressPartnerAdapterRegistry;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Order\useCases\CreateOrderFromIngressUseCase;
use App\Domain\AggregatorIngress\Exception\UnknownPartnerSkuException;
use App\Domain\AggregatorIngress\Repository\IngressAuditRepository;
use App\Domain\AggregatorIngress\Repository\PartnerCatalogBindingRepository;
use App\Domain\AggregatorIngress\ValueObject\IngressMappedLine;
use App\Domain\Order\Repository\OrderRepository;

/**
 * Единый pipeline приёма заказа от агрегатора.
 */
final class ReceiveExternalOrderUseCase
{
    public function __construct(
        private readonly IngressPartnerAuthenticator $authenticator,
        private readonly IngressPartnerAdapterRegistry $adapterRegistry,
        private readonly PartnerCatalogBindingRepository $catalogBindings,
        private readonly CreateOrderFromIngressUseCase $createOrderFromIngress,
        private readonly OrderRepository $orders,
        private readonly OrderPresenter $orderPresenter,
        private readonly IngressAuditRepository $audit,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ReceiveExternalOrderDto $input): array
    {
        $partnerCode = $input->partnerCode;

        $this->authenticator->authenticate($partnerCode, $input->apiKey);

        $adapter = $this->adapterRegistry->resolve($partnerCode);
        $externalOrderId = $this->extractExternalOrderId($adapter, $input->payload);

        $existingOrder = $this->resolveExistingOrder($input->partnerCode, $externalOrderId);
        if ($existingOrder !== null) {
            $this->audit->record(
                partnerCode: $partnerCode,
                externalOrderId: $externalOrderId,
                result: 'idempotent',
                rawPayload: $input->payload,
                orderId: $existingOrder['order_id'],
            );

            return $existingOrder;
        }

        try {
            $mapped = $adapter->map($input->payload);
            $resolvedLines = $this->resolveCatalogLines($partnerCode, $mapped->lines);
            $createDto = IngressMappedOrderToCreateOrderMapper::toCreateOrderDto(
                $partnerCode,
                $mapped,
                $resolvedLines,
            );
            $order = $this->createOrderFromIngress->execute($createDto);
            $presented = $this->orderPresenter->present($order);

            $this->audit->record(
                partnerCode: $partnerCode,
                externalOrderId: $mapped->externalOrderId,
                result: 'accepted',
                rawPayload: $input->payload,
                orderId: $presented['id'],
            );

            return [
                'order_id' => $presented['id'],
                'status' => 'accepted',
                'order' => $presented,
            ];
        } catch (\Throwable $e) {
            $this->audit->record(
                partnerCode: $partnerCode,
                externalOrderId: $externalOrderId,
                result: 'rejected',
                rawPayload: $input->payload,
            );

            throw $e;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveExistingOrder(string $partnerCode, string $externalOrderId): ?array
    {
        if ($externalOrderId === '') {
            return null;
        }

        $order = $this->orders->findByPartnerAndExternalOrderId(
            $partnerCode,
            $externalOrderId,
        );

        if ($order === null) {
            return null;
        }

        $presented = $this->orderPresenter->present($order);

        return [
            'order_id' => $presented['id'],
            'status' => 'accepted',
            'order' => $presented,
        ];
    }

    /**
     * @param  list<IngressMappedLine>  $lines
     * @return list<array{line: IngressMappedLine, product: \App\Domain\AggregatorIngress\ValueObject\ResolvedPartnerProduct}>
     */
    private function resolveCatalogLines(string $partnerCode, array $lines): array
    {
        $resolved = [];

        foreach ($lines as $line) {
            $product = $this->catalogBindings->resolve($partnerCode, $line->partnerSku);

            if ($product === null) {
                throw new UnknownPartnerSkuException($line->partnerSku);
            }

            $resolved[] = [
                'line' => $line,
                'product' => $product,
            ];
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractExternalOrderId(IngressPartnerAdapter $adapter, array $payload): string
    {
        try {
            return trim($adapter->extractExternalOrderId($payload));
        } catch (\Throwable) {
            return '';
        }
    }
}
