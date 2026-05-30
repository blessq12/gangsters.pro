<?php

namespace App\Providers;

use App\Domain\Admin\AdminAccess;
use App\Domain\Admin\Enums\AdminHub;
use App\Infrastructure\Auth\SanctumClientAuthContext;
use App\Infrastructure\Auth\SanctumClientTokenService;
use App\Models\User;
use App\Shared\Auth\ClientAuthContext;
use App\Shared\Auth\ClientTokenService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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

        Gate::define('admin', fn (User $user): bool => AdminAccess::isStaff($user));

        Gate::define('admin-hub', fn (User $user, AdminHub $hub): bool => AdminAccess::canAccessHub($user, $hub));

        Gate::define('admin-mutate', fn (User $user): bool => AdminAccess::canMutate($user));

        Gate::define('admin-manage-staff', fn (User $user): bool => AdminAccess::canManageStaff($user));
    }
}
