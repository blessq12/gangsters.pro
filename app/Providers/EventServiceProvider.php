<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Domain\Order\Events\OrderCreated::class => [
            \App\Infrastructure\Notifications\Client\OnOrderCreatedClientEmail::class,
        ],
        \App\Application\Order\Events\OrderCreatedIntegrationEvent::class => [
            \App\Infrastructure\Reporting\Listeners\UpsertClientOrderFact::class,
        ],
        \App\Application\Order\Events\OrderUpdatedIntegrationEvent::class => [
            \App\Infrastructure\Reporting\Listeners\UpsertClientOrderFact::class,
        ],
        \App\Application\Order\Events\OrderCancelledIntegrationEvent::class => [
            \App\Infrastructure\Reporting\Listeners\DeleteClientOrderFact::class,
            \App\Infrastructure\YandexFood\Listeners\DeleteYandexFoodOrderMeta::class,
        ],
        \App\Domain\Client\Events\ClientRegistered::class => [
            \App\Infrastructure\Client\Listeners\OnClientRegistered::class,
            \App\Infrastructure\Notifications\Client\OnClientRegisteredClientEmail::class,
            \App\Infrastructure\Reporting\Listeners\SyncClientProfileProjection::class,
        ],
        \App\Domain\Client\Events\ClientLoginFailed::class => [
            \App\Infrastructure\Client\Listeners\OnClientLoginFailed::class,
        ],
        \App\Domain\Client\Events\ClientUnauthorizedAccessDetected::class => [
            \App\Infrastructure\Client\Listeners\OnClientUnauthorizedAccessDetected::class,
        ],
        \App\Domain\Client\Events\ClientProfileUpdated::class => [
            \App\Infrastructure\Client\Listeners\OnClientProfileUpdated::class,
            \App\Infrastructure\Notifications\Client\OnClientProfileUpdatedClientEmail::class,
        ],
        \App\Domain\Client\Events\ClientAddressAdded::class => [
            \App\Infrastructure\Client\Listeners\OnClientAddressAdded::class,
            \App\Infrastructure\Reporting\Listeners\SyncClientProfileProjection::class,
        ],
        \App\Domain\Client\Events\ClientAddressDeleted::class => [
            \App\Infrastructure\Client\Listeners\OnClientAddressDeleted::class,
            \App\Infrastructure\Reporting\Listeners\SyncClientProfileProjection::class,
        ],
        \App\Domain\Client\Events\ClientPasswordChanged::class => [
            \App\Infrastructure\Client\Listeners\OnClientPasswordChanged::class,
        ],
        \App\Domain\Client\Events\ClientPasswordResetRequested::class => [
            \App\Infrastructure\Notifications\Client\OnClientPasswordResetRequested::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        if ((bool) config('services.frontpad.enabled', false)) {
            Event::listen(
                \App\Domain\Order\Events\OrderCreated::class,
                \App\Infrastructure\Order\Listeners\PushOrderToFrontpad::class,
            );
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
