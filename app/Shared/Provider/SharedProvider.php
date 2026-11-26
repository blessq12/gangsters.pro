<?php

namespace App\Shared\Provider;

use App\Application\Dashboard\Port\DashboardSummaryPort;
use App\Application\Shared\DomainEventPublisher;
use App\Infrastructure\Shared\Adapter\EloquentDashboardSummaryPort;
use App\Infrastructure\Shared\Event\LaravelDomainEventPublisher;
use App\Infrastructure\Shared\Transaction\LaravelTransactionManager;
use App\Shared\Transaction\TransactionManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class SharedProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
        $this->app->bind(DomainEventPublisher::class, LaravelDomainEventPublisher::class);
        $this->app->bind(DashboardSummaryPort::class, EloquentDashboardSummaryPort::class);
    }

    public function boot(): void
    {
        $this->registerDomainEventSubscriptions();
    }

    private function registerDomainEventSubscriptions(): void
    {
        /** @var array<class-string, list<class-string|array{0: class-string, 1: string}>> $listen */
        $listen = config('domain_events.listen', []);

        foreach ($listen as $event => $listeners) {
            if (! is_string($event) || ! is_array($listeners)) {
                continue;
            }

            foreach ($listeners as $listener) {
                if (is_string($listener)) {
                    Event::listen($event, [$listener, 'handle']);

                    continue;
                }

                if (
                    is_array($listener)
                    && isset($listener[0], $listener[1])
                    && is_string($listener[0])
                    && is_string($listener[1])
                ) {
                    Event::listen($event, [$listener[0], $listener[1]]);
                }
            }
        }
    }
}
