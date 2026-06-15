<?php

namespace App\Infrastructure\OrderAccountingExport\Adapter;

use App\Application\OrderAccountingExport\Mapper\FrontpadOrderMapper;
use App\Application\OrderAccountingExport\Port\AccountingSystemAdapter;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\Exception\UnknownAccountingProductException;
use App\Domain\OrderAccountingExport\ValueObject\ExportResult;
use App\Infrastructure\OrderAccountingExport\Client\FrontpadApiClient;

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
            return ExportResult::failed($exception->getMessage());
        }

        if (($response['result'] ?? null) !== 'success') {
            $error = (string) ($response['error'] ?? $response['message'] ?? 'unknown_error');

            return ExportResult::failed($error);
        }

        $externalReference = (string) ($response['order_id'] ?? $response['order_number'] ?? '');

        return ExportResult::success(
            externalReference: $externalReference !== '' ? $externalReference : null,
        );
    }
}
