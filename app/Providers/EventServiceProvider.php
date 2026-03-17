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
            // \App\Infrastructure\Order\Listeners\PushOrderToFrontpad::class, // интеграция с Frontpad (пока выключена)
        ],
        \App\Domain\Product\Events\ProductCreated::class => [
            \App\Infrastructure\Product\Listeners\OnProductCreated::class,
        ],
        \App\Domain\Product\Events\ProductUpdated::class => [
            \App\Infrastructure\Product\Listeners\OnProductUpdated::class,
        ],
        \App\Domain\Product\Events\ProductArchived::class => [
            \App\Infrastructure\Product\Listeners\OnProductArchived::class,
        ],
        \App\Domain\Client\Events\ClientRegistered::class => [
            \App\Infrastructure\Client\Listeners\OnClientRegistered::class,
        ],
        \App\Domain\Client\Events\ClientProfileUpdated::class => [
            \App\Infrastructure\Client\Listeners\OnClientProfileUpdated::class,
        ],
        \App\Domain\Client\Events\ClientAddressAdded::class => [
            \App\Infrastructure\Client\Listeners\OnClientAddressAdded::class,
        ],
        \App\Domain\Client\Events\ClientAddressDeleted::class => [
            \App\Infrastructure\Client\Listeners\OnClientAddressDeleted::class,
        ],
        \App\Domain\Client\Events\ClientPasswordChanged::class => [
            \App\Infrastructure\Client\Listeners\OnClientPasswordChanged::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
