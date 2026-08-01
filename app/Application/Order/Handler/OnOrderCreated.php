<?php

namespace App\Application\Order\Handler;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Port\FrontpadOrderExporter;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Observer: after OrderCreated — best-effort Frontpad export.
 */
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
                'order_id' => $event->orderId()->value(),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
