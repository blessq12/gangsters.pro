<?php

use App\Domain\Crm\Event\ClientCreated;
use App\Domain\Crm\Event\ClientPasswordChanged;
use App\Domain\Order\Event\OrderCreated;
use App\Infrastructure\Crm\Listener\RecordOrderHistoryOnCreated;
use App\Infrastructure\Crm\Listener\SendWelcomeMailOnClientCreated;
use App\Infrastructure\Order\Listener\SendOrderConfirmationMailOnOrderCreated;
use App\Integration\Frontpad\Listener\OnOrderCreated;

/**
 * Матрица подписок: доменное событие → слушатели Integration / Infrastructure.
 *
 * Слушатель: class-string с методом handle, или [class-string, 'method'].
 *
 * @return array{
 *     listen: array<class-string, list<class-string|array{0: class-string, 1: string}>>
 * }
 */
return [
    'listen' => [
        OrderCreated::class => [
            OnOrderCreated::class, // frontpad integration
            RecordOrderHistoryOnCreated::class,
            SendOrderConfirmationMailOnOrderCreated::class,
        ],

        ClientCreated::class => [
            SendWelcomeMailOnClientCreated::class,
        ],

        ClientPasswordChanged::class => [
            // слушатели подписок CRM
        ],
    ],
];
