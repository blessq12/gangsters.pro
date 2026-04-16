<?php

namespace App\Application\Order;

use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Shared\Auth\ClientAuthContext;
use App\Shared\Events\DomainEventBus;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Application\Order\Presenter\OrderPresenter;

abstract class OrderBaseUseCase
{
    public function __construct(
        protected readonly OrderRepositoryInterface $orders,
        protected readonly OrderFactory $orderFactory,
        protected readonly CustomerSnapshotProvider $customerSnapshots,
        protected readonly ClientAuthContext $authContext,
        protected readonly OrderItemsFactory $itemsFactory,
        protected readonly OrderPresenter $presenter,
        protected readonly DomainEventBus $events,
    ) {
    }
}
