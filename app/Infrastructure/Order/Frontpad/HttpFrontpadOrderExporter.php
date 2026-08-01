<?php

namespace App\Infrastructure\Order\Frontpad;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Port\FrontpadOrderExporter;
use Illuminate\Support\Facades\Log;

final class HttpFrontpadOrderExporter implements FrontpadOrderExporter
{
    public function __construct(
        private readonly FrontpadOrderMapper $mapper,
        private readonly FrontpadApiClient $client,
    ) {}

    public function export(OrderCreated $event): void
    {
        if (! $this->isEnabled()) {
            Log::debug('Frontpad export skipped (disabled or secret empty)', [
                'order_id' => $event->orderId()->value(),
            ]);

            return;
        }

        $request = $this->mapper->toRequest($event);
        $response = $this->client->createOrder($request);

        if (($response['result'] ?? null) !== 'success') {
            $error = (string) ($response['error'] ?? $response['message'] ?? 'unknown_error');

            Log::error('Frontpad export rejected', [
                'order_id' => $event->orderId()->value(),
                'error' => $error,
                'response' => $response,
            ]);

            return;
        }

        if (isset($response['warnings'])) {
            Log::warning('Frontpad export warnings', [
                'order_id' => $event->orderId()->value(),
                'warnings' => $response['warnings'],
            ]);
        }

        Log::info('Frontpad order created', [
            'order_id' => $event->orderId()->value(),
            'frontpad_order_id' => $response['order_id'] ?? null,
            'frontpad_order_number' => $response['order_number'] ?? null,
        ]);
    }

    private function isEnabled(): bool
    {
        if (! (bool) config('frontpad.enabled', false)) {
            return false;
        }

        return (string) config('frontpad.secret', '') !== '';
    }
}
