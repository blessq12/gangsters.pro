<?php

namespace App\Infrastructure\OrderAccountingExport\Adapter;

use App\Application\OrderAccountingExport\Mapper\IikoOrderMapper;
use App\Application\OrderAccountingExport\Port\AccountingSystemAdapter;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\Exception\UnknownAccountingProductException;
use App\Domain\OrderAccountingExport\ValueObject\ExportResult;
use App\Infrastructure\OrderAccountingExport\Client\IikoApiClient;

final class IikoAccountingSystemAdapter implements AccountingSystemAdapter
{
    public function __construct(
        private readonly IikoOrderMapper $mapper,
        private readonly IikoApiClient $client,
    ) {}

    public function systemCode(): string
    {
        return 'iiko';
    }

    public function isEnabled(): bool
    {
        if (! (bool) config('order-accounting-export.systems.iiko.enabled', false)) {
            return false;
        }

        $apiLogin = (string) config('order-accounting-export.systems.iiko.api_login', '');
        $organizationId = (string) config('order-accounting-export.systems.iiko.organization_id', '');
        $terminalGroupId = (string) config('order-accounting-export.systems.iiko.terminal_group_id', '');

        return $apiLogin !== '' && $organizationId !== '' && $terminalGroupId !== '';
    }

    public function supports(OrderCreated $event): bool
    {
        return true;
    }

    public function export(OrderCreated $event): ExportResult
    {
        try {
            $request = $this->mapper->toRequest($event);
            $response = $this->client->createDelivery($request);
        } catch (UnknownAccountingProductException $exception) {
            return ExportResult::failed($exception->getMessage());
        } catch (\Throwable $exception) {
            return ExportResult::failed($exception->getMessage());
        }

        if (($response['httpStatus'] ?? 0) >= 400) {
            $error = (string) ($response['errorDescription'] ?? $response['message'] ?? 'http_error');

            return ExportResult::failed($error);
        }

        $orderInfo = is_array($response['orderInfo'] ?? null) ? $response['orderInfo'] : [];
        $externalReference = (string) ($orderInfo['id'] ?? $response['correlationId'] ?? '');

        return ExportResult::success(
            externalReference: $externalReference !== '' ? $externalReference : null,
        );
    }
}
