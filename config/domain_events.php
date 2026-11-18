<?php

use App\Domain\Catalog\Event\CatalogItemPriceChanged;
use App\Domain\Delivery\Event\DeliveryZonesChanged;
use App\Domain\Delivery\Event\OrderAssignedToRoute;
use App\Domain\Delivery\Event\RouteSheetTaken;
use App\Domain\Delivery\Event\RouteStopSettled;
use App\Domain\Order\Event\OrderCancelled;
use App\Domain\Order\Event\OrderPlaced;
use App\Domain\Payment\Event\PaymentInitiated;
use App\Domain\Production\Event\KitchenTicketCompleted;
use App\Domain\Production\Event\KitchenTicketStarted;
use App\Infrastructure\Delivery\Listener\CancelRouteStopOnOrderCancelled;
use App\Infrastructure\Order\Listener\AttachPaymentOnPaymentInitiated;
use App\Infrastructure\Order\Listener\MarkOrderAssignedToRouteOnOrderAssignedToRoute;
use App\Infrastructure\Payment\Listener\InitiatePaymentOnOrderPlaced;
use App\Infrastructure\Order\Listener\MarkOrderPreparingOnKitchenTicketStarted;
use App\Infrastructure\Order\Listener\MarkOrderReadyOnKitchenTicketCompleted;
use App\Infrastructure\Order\Listener\MarkOrdersInTransitOnRouteSheetTaken;
use App\Infrastructure\Order\Listener\SettleOrderOnRouteStopSettled;
use App\Infrastructure\Production\Listener\CancelKitchenTicketOnOrderCancelled;
use App\Infrastructure\Promotion\Listener\RefreshRoleSnapshotsOnCatalogItemPriceChanged;
use App\Infrastructure\Promotion\Listener\ReplaceZoneSnapshotsOnDeliveryZonesChanged;

/**
 * Матрица подписок: доменное событие → слушатели Infrastructure.
 *
 * Слушатель: class-string с методом handle, или [class-string, 'method'].
 *
 * @return array{
 *     listen: array<class-string, list<class-string|array{0: class-string, 1: string}>>
 * }
 */
return [
    'listen' => [
        KitchenTicketStarted::class => [
            MarkOrderPreparingOnKitchenTicketStarted::class,
        ],

        KitchenTicketCompleted::class => [
            MarkOrderReadyOnKitchenTicketCompleted::class,
        ],

        OrderCancelled::class => [
            CancelKitchenTicketOnOrderCancelled::class,
            CancelRouteStopOnOrderCancelled::class,
        ],

        OrderAssignedToRoute::class => [
            MarkOrderAssignedToRouteOnOrderAssignedToRoute::class,
        ],

        RouteSheetTaken::class => [
            MarkOrdersInTransitOnRouteSheetTaken::class,
        ],

        RouteStopSettled::class => [
            SettleOrderOnRouteStopSettled::class,
        ],

        CatalogItemPriceChanged::class => [
            RefreshRoleSnapshotsOnCatalogItemPriceChanged::class,
        ],

        DeliveryZonesChanged::class => [
            ReplaceZoneSnapshotsOnDeliveryZonesChanged::class,
        ],

        OrderPlaced::class => [
            InitiatePaymentOnOrderPlaced::class,
        ],

        PaymentInitiated::class => [
            AttachPaymentOnPaymentInitiated::class,
        ],
    ],
];
