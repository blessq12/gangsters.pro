<?php

namespace App\Infrastructure\Reporting\Query;

use App\Application\Reporting\Query\ClientOrderSummaryReader;
use App\Infrastructure\Reporting\Model\ReportingClientOrderFact;
use App\Infrastructure\Reporting\Model\ReportingClientProfile;
use DateTimeImmutable;

final class EloquentClientOrderSummaryReader implements ClientOrderSummaryReader
{
    public function getSummaryById(int $clientId): ?array
    {
        $profile = ReportingClientProfile::query()->find($clientId);
        if ($profile === null) {
            return null;
        }

        $ordersQuery = ReportingClientOrderFact::query()->where('client_id', $clientId);
        $ordersCount = (int) $ordersQuery->count();
        $paidOrdersCount = (int) ReportingClientOrderFact::query()
            ->where('client_id', $clientId)
            ->where('payment_status', 'paid')
            ->count();
        $ordersTotal = (int) ReportingClientOrderFact::query()
            ->where('client_id', $clientId)
            ->sum('total');
        $lastOrderAt = ReportingClientOrderFact::query()
            ->where('client_id', $clientId)
            ->latest('created_at')
            ->value('created_at');

        return [
            'client_id' => (int) $profile->client_id,
            'orders_count' => $ordersCount,
            'paid_orders_count' => $paidOrdersCount,
            'orders_total' => $ordersTotal,
            'average_order_total' => $ordersCount > 0 ? (int) floor($ordersTotal / $ordersCount) : 0,
            'last_order_at' => $lastOrderAt ? (new DateTimeImmutable((string) $lastOrderAt))->format(DATE_ATOM) : null,
            'addresses_count' => (int) $profile->addresses_count,
        ];
    }
}
