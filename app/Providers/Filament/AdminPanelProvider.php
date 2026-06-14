<?php

namespace App\Providers\Filament;

use App\Filament\Catalog\Resources\CatalogResource;
use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Resources\CategoryResource\RelationManagers\CategoryProductsRelationManager;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Resources\ProductResource\RelationManagers\ProductImagesRelationManager;
use App\Filament\Catalog\Resources\ProductSetResource;
use App\Filament\Catalog\Resources\ProductSetResource\RelationManagers\ProductSetLinesRelationManager;
use App\Filament\Catalog\Resources\TagResource;
use App\Filament\Catalog\Widgets\Tables\CategoriesHubTable;
use App\Filament\Catalog\Widgets\Tables\ProductSetsHubTable;
use App\Filament\Catalog\Widgets\Tables\ProductsHubTable;
use App\Filament\Catalog\Widgets\Tables\TagsHubTable;
use App\Filament\MarketingContent\Resources\BannerResource;
use App\Filament\MarketingContent\Resources\MarketingContentResource;
use App\Filament\MarketingContent\Resources\PromotionResource;
use App\Filament\MarketingContent\Widgets\Tables\BannersHubTable;
use App\Filament\MarketingContent\Widgets\Tables\PromotionsHubTable;
use App\Filament\Company\Resources\CompanyResource;
use App\Filament\Company\Resources\OperatorResource;
use App\Filament\Checkout\Resources\CheckoutResource;
use App\Filament\Client\Resources\ClientResource;
use App\Filament\Order\Resources\OrderResource;
use App\Filament\Delivery\Resources\DeliveryResource;
use App\Filament\Promotion\Resources\PromotionPolicyResource;
use App\Filament\Support\AdminNavigationGroup;
use App\Http\Controllers\Admin\DeliveryZoneMapEditorController;
use App\Http\Middleware\VerifyCsrfToken;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups(AdminNavigationGroup::class)
            ->resources([
                CatalogResource::class,
                CategoryResource::class,
                ProductResource::class,
                ProductSetResource::class,
                TagResource::class,
                MarketingContentResource::class,
                BannerResource::class,
                PromotionResource::class,
                DeliveryResource::class,
                PromotionPolicyResource::class,
                OrderResource::class,
                CheckoutResource::class,
                ClientResource::class,
                CompanyResource::class,
                OperatorResource::class,
            ])
            ->livewireComponents([
                CategoriesHubTable::class,
                ProductsHubTable::class,
                ProductSetsHubTable::class,
                TagsHubTable::class,
                CategoryProductsRelationManager::class,
                ProductImagesRelationManager::class,
                ProductSetLinesRelationManager::class,
                BannersHubTable::class,
                PromotionsHubTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->routes(function (): void {
                Route::get('/delivery-zone-map-editor', DeliveryZoneMapEditorController::class)
                    ->name('delivery-zone-map-editor');
            })
            ->assets([
                Js::make('delivery-zone-bridge', asset('js/filament/delivery-zone-iframe-bridge.js')),
            ]);
    }
}
