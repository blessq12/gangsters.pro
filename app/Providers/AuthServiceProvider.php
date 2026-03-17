<?php

namespace App\Providers;

use App\Shared\Auth\ClientAuthContext;
use App\Shared\Auth\ClientTokenService;
use App\Infrastructure\Auth\SanctumClientAuthContext;
use App\Infrastructure\Auth\SanctumClientTokenService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->app->bind(ClientAuthContext::class, SanctumClientAuthContext::class);
        $this->app->bind(ClientTokenService::class, SanctumClientTokenService::class);

        Gate::define('admin', function(User $user){
            return $user->admin;
        });
    }
}
