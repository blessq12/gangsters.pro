<?php

namespace App\Infrastructure\Order\Provider;

use App\Application\Order\Checkout\CheckoutQuote;
use App\Application\Order\Checkout\CheckoutRulesVersion;
use App\Application\Order\Query\GetCheckoutContextUseCase;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Domain\Core\Order\Port\OrderBranchCommercialRulesPort;
use App\Domain\Core\Order\Port\OrderBranchLookupPort;
use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Core\Order\Port\OrderClientLookupPort;
use App\Domain\Core\Order\Port\OrderDeliveryTariffPort;
use App\Domain\Core\Order\Port\OrderPaymentSnapshotPort;
use App\Domain\Core\Order\Repository\OrderAuditRepository;
use App\Domain\Core\Order\Repository\OrderNoteRepository;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Adapter\EloquentOrderBranchCommercialRulesPort;
use App\Infrastructure\Order\Adapter\EloquentOrderBranchLookupPort;
use App\Infrastructure\Order\Adapter\EloquentOrderBranchMenuSnapshotPort;
use App\Infrastructure\Order\Adapter\EloquentOrderCatalogSnapshotPort;
use App\Infrastructure\Order\Adapter\EloquentOrderClientLookupPort;
use App\Infrastructure\Order\Adapter\EloquentOrderDeliveryTariffPort;
use App\Infrastructure\Order\Adapter\EloquentOrderPaymentSnapshotPort;
use App\Infrastructure\Order\Repository\EloquentOrderAuditRepository;
use App\Infrastructure\Order\Repository\EloquentOrderNoteRepository;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(OrderNoteRepository::class, EloquentOrderNoteRepository::class);
        $this->app->bind(OrderAuditRepository::class, EloquentOrderAuditRepository::class);
        $this->app->bind(CheckoutQuote::class, QuoteOrderUseCase::class);
        $this->app->bind(CheckoutRulesVersion::class, GetCheckoutContextUseCase::class);
        $this->app->bind(OrderPaymentSnapshotPort::class, EloquentOrderPaymentSnapshotPort::class);
        $this->app->bind(OrderCatalogSnapshotPort::class, EloquentOrderCatalogSnapshotPort::class);
        $this->app->bind(OrderBranchMenuSnapshotPort::class, EloquentOrderBranchMenuSnapshotPort::class);
        $this->app->bind(OrderDeliveryTariffPort::class, EloquentOrderDeliveryTariffPort::class);
        $this->app->bind(OrderClientLookupPort::class, EloquentOrderClientLookupPort::class);
        $this->app->bind(OrderBranchLookupPort::class, EloquentOrderBranchLookupPort::class);
        $this->app->bind(OrderBranchCommercialRulesPort::class, EloquentOrderBranchCommercialRulesPort::class);
    }
}
