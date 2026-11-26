<?php

namespace App\Infrastructure\Menu\Provider;

use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Port\MenuCatalogLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Menu\Repository\BranchMenuRepository;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;
use App\Domain\Supporting\Menu\Repository\BranchTagRepository;
use App\Infrastructure\Menu\Adapter\EloquentMenuBranchLookupPort;
use App\Infrastructure\Menu\Adapter\EloquentMenuCatalogLookupPort;
use App\Infrastructure\Menu\Repository\EloquentBranchCategoryRepository;
use App\Infrastructure\Menu\Repository\EloquentBranchMenuRepository;
use App\Infrastructure\Menu\Repository\EloquentBranchSetRepository;
use App\Infrastructure\Menu\Repository\EloquentBranchTagRepository;
use Illuminate\Support\ServiceProvider;

final class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BranchMenuRepository::class, EloquentBranchMenuRepository::class);
        $this->app->bind(BranchCategoryRepository::class, EloquentBranchCategoryRepository::class);
        $this->app->bind(BranchTagRepository::class, EloquentBranchTagRepository::class);
        $this->app->bind(BranchSetRepository::class, EloquentBranchSetRepository::class);
        $this->app->bind(MenuCatalogLookupPort::class, EloquentMenuCatalogLookupPort::class);
        $this->app->bind(MenuBranchLookupPort::class, EloquentMenuBranchLookupPort::class);
    }
}
