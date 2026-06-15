<?php

namespace App\Application\Order\OrderDraft\useCases;

use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\OrderDraft\DTO\PlaceOrderInput;
use App\Application\Order\OrderDraft\Mapper\OrderDraftToCreateOrderMapper;
use App\Application\Order\OrderDraft\Services\BuildOrderDraftFromInput;
use App\Application\Order\OrderDraft\Services\ProcessOrderDraftPipeline;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Order\useCases\CreateOrderUseCase;
use App\Domain\Order\Entity\Order;

/**
 * Сценарий: authoritative создание заказа из черновика.
 */
final class PlaceOrderUseCase
{
    public function __construct(
        private readonly BuildOrderDraftFromInput $buildDraft,
        private readonly ProcessOrderDraftPipeline $pipeline,
        private readonly CreateOrderUseCase $createOrder,
        private readonly OrderPresenter $orderPresenter,
    ) {}

    /**
     * @return array{order: array<string, mixed>}
     */
    public function execute(PlaceOrderInput $input): array
    {
        $draft = $this->buildDraft->build($input->draft);
        $draft = $this->pipeline->process($draft, forPlace: true);

        $createDto = OrderDraftToCreateOrderMapper::toCreateOrderDto(
            draft: $draft,
            clientRequestId: $input->clientRequestId,
            createdAt: new \DateTimeImmutable(),
        );

        $order = $this->createOrder->execute($createDto);

        return [
            'order' => $this->orderPresenter->present($order),
        ];
    }
}
