<?php

namespace App\Providers;

use App\Domain\Client\Port\ClientAuthTokenPort;
use App\Domain\Client\Port\ClientPasswordResetNotifierPort;
use App\Domain\Client\Port\ClientPasswordResetTokenStorePort;
use App\Domain\Client\Repository\ClientRepository;
use App\Infrastructure\Client\Auth\EloquentClientPasswordResetTokenStore;
use App\Infrastructure\Client\Auth\SanctumClientAuthTokenAdapter;
use App\Infrastructure\Client\Notification\ClientPasswordResetNotifier;
use App\Infrastructure\Client\Repository\EloquentClientRepository;
use Illuminate\Support\ServiceProvider;

final class ClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepository::class, EloquentClientRepository::class);
        $this->app->bind(ClientAuthTokenPort::class, SanctumClientAuthTokenAdapter::class);
        $this->app->bind(ClientPasswordResetTokenStorePort::class, EloquentClientPasswordResetTokenStore::class);
        $this->app->bind(ClientPasswordResetNotifierPort::class, ClientPasswordResetNotifier::class);
    }
}
