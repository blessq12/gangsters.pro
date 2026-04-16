<?php

namespace App\Infrastructure\YandexFood\Listeners;

use App\Application\Order\Events\OrderCancelledIntegrationEvent;
use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;

final class DeleteYandexFoodOrderMeta
{
    public function __construct(
        private readonly YandexFoodOrderMetaStore $metaStore,
    ) {
    }

    public function handle(OrderCancelledIntegrationEvent $event): void
    {
        $this->metaStore->deleteByOrderId($event->orderId);
    }
}
