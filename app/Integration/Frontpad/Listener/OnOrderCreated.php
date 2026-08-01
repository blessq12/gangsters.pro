<?php

namespace App\Integration\Frontpad\Listener;

use App\Domain\Order\Event\OrderCreated;
use App\Integration\Frontpad\FrontpadOrderExporter;
use Illuminate\Support\Facades\Log;
use Throwable;

final class OnOrderCreated
{
    public function __construct(
        private readonly FrontpadOrderExporter $frontpadOrderExporter,
    ) {}

    public function handle(OrderCreated $event): void
    {
        try {
            $this->frontpadOrderExporter->export($event);
        } catch (Throwable $exception) {
            Log::error('Frontpad export failed after OrderCreated', [
                'order_id' => $event->orderId(),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
