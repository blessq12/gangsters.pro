<?php

namespace App\Infrastructure\OrderAccountingExport\Adapter;

use App\Application\OrderAccountingExport\Mapper\FrontpadOrderMapper;
use App\Application\OrderAccountingExport\Port\AccountingSystemAdapter;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\Exception\UnknownAccountingProductException;
use App\Domain\OrderAccountingExport\ValueObject\ExportResult;
use App\Infrastructure\OrderAccountingExport\Client\FrontpadApiClient;
use Illuminate\Support\Facades\Log;

final class FrontpadAccountingSystemAdapter implements AccountingSystemAdapter
{
    public function __construct(
        private readonly FrontpadOrderMapper $mapper,
        private readonly FrontpadApiClient $client,
    ) {}

    public function systemCode(): string
    {
        return 'frontpad';
    }

    public function isEnabled(): bool
    {
        if (! (bool) config('order-accounting-export.systems.frontpad.enabled', false)) {
            return false;
        }

        $secret = (string) config('order-accounting-export.systems.frontpad.secret', '');

        return $secret !== '';
    }

    public function supports(OrderCreated $event): bool
    {
        return true;
    }

    public function export(OrderCreated $event): ExportResult
    {
        try {
            $request = $this->mapper->toRequest($event);
            $response = $this->client->createOrder($request);
        } catch (UnknownAccountingProductException $exception) {
            return ExportResult::failed($exception->getMessage());
        } catch (\Throwable $exception) {
            Log::error('Frontpad export failed', [
                'order_id' => $event->orderId()->value(),
                'message' => $exception->getMessage(),
            ]);

            return ExportResult::failed($exception->getMessage());
        }

        if (($response['result'] ?? null) !== 'success') {
            $error = (string) ($response['error'] ?? $response['message'] ?? 'unknown_error');

            Log::error('Frontpad export rejected', [
                'order_id' => $event->orderId()->value(),
                'error' => $error,
                'response' => $response,
            ]);

            return ExportResult::failed($error);
        }

        if (isset($response['warnings'])) {
            Log::warning('Frontpad export warnings', [
                'order_id' => $event->orderId()->value(),
                'warnings' => $response['warnings'],
            ]);
        }

        $externalReference = (string) ($response['order_id'] ?? $response['order_number'] ?? '');

        if ($externalReference !== '') {
            Log::info('Frontpad order created', [
                'order_id' => $event->orderId()->value(),
                'frontpad_order_id' => $response['order_id'] ?? null,
                'frontpad_order_number' => $response['order_number'] ?? null,
            ]);
        }

        return ExportResult::success(
            externalReference: $externalReference !== '' ? $externalReference : null,
        );
    }
}
