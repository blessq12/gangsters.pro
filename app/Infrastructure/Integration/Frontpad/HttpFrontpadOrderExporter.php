<?php

namespace App\Infrastructure\Integration\Frontpad;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Repository\OrderRepository;
use App\Integration\Frontpad\FrontpadOrderExporter;
use Illuminate\Support\Facades\Log;
use Throwable;

final class HttpFrontpadOrderExporter implements FrontpadOrderExporter
{
    public function __construct(
        private readonly FrontpadOrderMapper $mapper,
        private readonly FrontpadApiClient $client,
        private readonly OrderRepository $orders,
    ) {}

    public function export(OrderCreated $event): void
    {
        if (! $this->isEnabled()) {
            Log::debug('Frontpad export skipped (disabled or secret empty)', [
                'order_id' => $event->orderId(),
            ]);

            return;
        }

        $request = $this->mapper->toRequest($event);
        $response = $this->client->createOrder($request);

        if (($response['result'] ?? null) !== 'success') {
            $error = (string) ($response['error'] ?? $response['message'] ?? 'unknown_error');

            Log::error('Frontpad export rejected', [
                'order_id' => $event->orderId(),
                'error' => $error,
                'response' => $response,
            ]);

            return;
        }

        if (isset($response['warnings'])) {
            Log::warning('Frontpad export warnings', [
                'order_id' => $event->orderId(),
                'warnings' => $response['warnings'],
            ]);
        }

        Log::info('Frontpad order created', [
            'order_id' => $event->orderId(),
            'frontpad_order_id' => $response['order_id'] ?? null,
            'frontpad_order_number' => $response['order_number'] ?? null,
        ]);

        $this->persistFrontpadOrderId($event, $response);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function persistFrontpadOrderId(OrderCreated $event, array $response): void
    {
        if (! isset($response['order_id'])) {
            return;
        }

        $frontpadId = (string) $response['order_id'];
        if ($frontpadId === '') {
            return;
        }

        try {
            $order = $this->orders->findById($event->orderId());
            if ($order === null) {
                return;
            }

            $this->orders->save($order->withFrontpadOrderId($frontpadId));
        } catch (Throwable $exception) {
            Log::error('Frontpad order id persist failed', [
                'order_id' => $event->orderId(),
                'frontpad_order_id' => $frontpadId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function isEnabled(): bool
    {
        if (! (bool) config('frontpad.enabled', false)) {
            return false;
        }

        return (string) config('frontpad.secret', '') !== '';
    }
}
