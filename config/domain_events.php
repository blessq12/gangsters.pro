<?php

use App\Domain\Core\Delivery\Event\OrderAddedToRoute;
use App\Domain\Core\Delivery\Event\RouteSheetTaken;
use App\Domain\Core\Delivery\Event\RouteStopSettled;
use App\Domain\Core\Order\Event\OrderCancelled;
use App\Domain\Core\Order\Event\OrderConfirmed;
use App\Domain\Core\Order\Event\OrderLinesChanged;
use App\Domain\Core\Order\Event\OrderPickedUp;
use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Core\Production\Event\KitchenTicketLinePacked;
use App\Domain\Core\Production\Event\KitchenTicketStarted;
use App\Domain\Supporting\Payment\Event\PaymentInitiated;
use App\Infrastructure\Delivery\Listener\CancelRouteStopOnOrderCancelled;
use App\Infrastructure\Order\Listener\AttachPaymentOnPaymentInitiated;
use App\Infrastructure\Order\Listener\MarkOrderAssignedToRouteOnOrderAddedToRoute;
use App\Infrastructure\Order\Listener\MarkOrderPackedOnKitchenTicketFullyPacked;
use App\Infrastructure\Order\Listener\MarkOrderPreparingOnKitchenTicketStarted;
use App\Infrastructure\Order\Listener\MarkOrderReadyOnKitchenTicketCompleted;
use App\Infrastructure\Order\Listener\MarkOrdersInTransitOnRouteSheetTaken;
use App\Infrastructure\Order\Listener\SettleOrderOnRouteStopSettled;
use App\Infrastructure\Payment\Listener\ClosePaymentOnOrderCancelled;
use App\Infrastructure\Payment\Listener\InitiatePaymentOnOrderConfirmed;
use App\Infrastructure\Payment\Listener\MarkPaymentPaidOnOrderPickedUp;
use App\Infrastructure\Payment\Listener\UpdatePaymentOnRouteStopSettled;
use App\Infrastructure\Production\Listener\CancelKitchenTicketOnOrderCancelled;
use App\Infrastructure\Production\Listener\CreateKitchenTicketOnOrderConfirmed;
use App\Infrastructure\Production\Listener\ResyncKitchenTicketOnOrderLinesChanged;
use App\Infrastructure\Warehouse\Listener\WriteOffStockOnKitchenTicketCompleted;

/**
 * Матрица подписок: доменное событие → слушатели Infrastructure.
 * Только события с реальными слушателями. Write между BC — только через эту матрицу.
 *
 * @return array{
 *     listen: array<class-string, list<class-string|array{0: class-string, 1: string}>>
 * }
 */
return [
    'listen' => [
        OrderConfirmed::class => [
            CreateKitchenTicketOnOrderConfirmed::class,
            InitiatePaymentOnOrderConfirmed::class,
        ],

        OrderLinesChanged::class => [
            ResyncKitchenTicketOnOrderLinesChanged::class,
        ],

        OrderPickedUp::class => [
            MarkPaymentPaidOnOrderPickedUp::class,
        ],

        KitchenTicketStarted::class => [
            MarkOrderPreparingOnKitchenTicketStarted::class,
        ],

        KitchenTicketCompleted::class => [
            MarkOrderReadyOnKitchenTicketCompleted::class,
            WriteOffStockOnKitchenTicketCompleted::class,
        ],

        KitchenTicketLinePacked::class => [
            MarkOrderPackedOnKitchenTicketFullyPacked::class,
        ],

        OrderAddedToRoute::class => [
            MarkOrderAssignedToRouteOnOrderAddedToRoute::class,
        ],

        RouteSheetTaken::class => [
            MarkOrdersInTransitOnRouteSheetTaken::class,
        ],

        RouteStopSettled::class => [
            SettleOrderOnRouteStopSettled::class,
            UpdatePaymentOnRouteStopSettled::class,
        ],

        OrderCancelled::class => [
            CancelKitchenTicketOnOrderCancelled::class,
            CancelRouteStopOnOrderCancelled::class,
            ClosePaymentOnOrderCancelled::class,
        ],

        PaymentInitiated::class => [
            AttachPaymentOnPaymentInitiated::class,
        ],
    ],
];
