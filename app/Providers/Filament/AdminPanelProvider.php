<?php

namespace App\Providers\Filament;

use App\Filament\Analytics\Pages\ManageAnalytics;
use App\Filament\Analytics\Widgets\Charts\DeliveryMixChartWidget;
use App\Filament\Analytics\Widgets\Charts\OrdersCountChartWidget;
use App\Filament\Analytics\Widgets\Charts\PaymentMixChartWidget;
use App\Filament\Analytics\Widgets\Charts\RevenueTrendChartWidget;
use App\Filament\Analytics\Widgets\Hub\HubClientsPanel;
use App\Filament\Analytics\Widgets\Hub\HubFinancePanel;
use App\Filament\Analytics\Widgets\Hub\HubOrdersPanel;
use App\Filament\Analytics\Widgets\Hub\HubOverviewPanel;
use App\Filament\Analytics\Widgets\Hub\HubStorefrontPanel;
use App\Filament\Analytics\Widgets\Stats\ChannelStatsWidget;
use App\Filament\Analytics\Widgets\Stats\ClientsKpiStatsWidget;
use App\Filament\Analytics\Widgets\Stats\FinanceRevenueStatsWidget;
use App\Filament\Analytics\Widgets\Stats\OrdersPipelineStatsWidget;
use App\Filament\Analytics\Widgets\Stats\OverviewPipelineStatsWidget;
use App\Filament\Analytics\Widgets\Stats\OverviewRevenueStatsWidget;
use App\Filament\Analytics\Widgets\Stats\ShoppingFunnelStatsWidget;
use App\Filament\Analytics\Widgets\Tables\RecentOrdersTableWidget;
use App\Filament\Analytics\Widgets\Tables\TopClientsTableWidget;
use App\Filament\Analytics\Widgets\Tables\TopProductsTableWidget;
use App\Filament\Catalog\Pages\ManageCatalog;
use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Resources\TagResource;
use App\Filament\Catalog\Tables\HubCategoriesTable;
use App\Filament\Catalog\Tables\HubLayoutTable;
use App\Filament\Catalog\Tables\HubProductsTable;
use App\Filament\Catalog\Tables\HubTagsTable;
use App\Filament\Catalog\Widgets\CatalogOverviewWidget;
use App\Filament\Company\Pages\ManageCompany;
use App\Filament\Company\Resources\DocumentResource;
use App\Filament\Company\Resources\StaffUserResource;
use App\Filament\Company\Tables\HubDocumentsTable;
use App\Filament\Company\Tables\HubStaffTable;
use App\Filament\Company\Widgets\HubCompanyLegalPanel;
use App\Filament\Company\Widgets\HubCompanyProfilePanel;
use App\Filament\Marketing\Pages\ManageMarketing;
use App\Filament\Marketing\Resources\BannerResource;
use App\Filament\Marketing\Resources\PromotionResource;
use App\Filament\Marketing\Tables\HubBannersTable;
use App\Filament\Marketing\Tables\HubPromotionsTable;
use App\Filament\Operations\Pages\ManageOperations;
use App\Filament\Operations\Resources\ClientResource;
use App\Filament\Operations\Resources\OrderResource;
use App\Filament\Operations\Tables\HubCartRulesProductsTable;
use App\Filament\Operations\Tables\HubClientsTable;
use App\Filament\Operations\Tables\HubOrdersTable;
use App\Filament\Operations\Widgets\HubCartRulesPanel;
use App\Filament\Operations\Widgets\HubDeliveryZonePanel;
use App\Http\Controllers\Admin\DeliveryZoneMapEditorController;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->pages([
                ManageAnalytics::class,
                ManageCatalog::class,
                ManageOperations::class,
                ManageCompany::class,
                ManageMarketing::class,
            ])
            ->resources([
                ProductResource::class,
                CategoryResource::class,
                TagResource::class,
                OrderResource::class,
                ClientResource::class,
                DocumentResource::class,
                StaffUserResource::class,
                BannerResource::class,
                PromotionResource::class,
            ])
            ->routes(function (): void {
                Route::get('/delivery-zone-map-editor', DeliveryZoneMapEditorController::class)
                    ->middleware(Authenticate::class)
                    ->name('delivery-zone-map-editor');

                Route::get('/operations/delivery-zone', fn () => abort(404));
                Route::get('/operations/cart-rules', fn () => abort(404));
                Route::get('/companies', fn () => abort(404));
                Route::get('/companies/{any}', fn () => abort(404))->where('any', '.*');
                Route::get('/users', fn () => abort(404));
                Route::get('/users/{any}', fn () => abort(404))->where('any', '.*');
                Route::get('/banners', fn () => abort(404));
                Route::get('/banners/{any}', fn () => abort(404))->where('any', '.*');
                Route::get('/company/banners', fn () => abort(404));
                Route::get('/company/banners/{any}', fn () => abort(404))->where('any', '.*');
                Route::get('/company/promotions', fn () => abort(404));
                Route::get('/company/promotions/{any}', fn () => abort(404))->where('any', '.*');
            })
            ->widgets([
                AccountWidget::class,
                HubOrdersTable::class,
                HubClientsTable::class,
                HubCartRulesPanel::class,
                HubCartRulesProductsTable::class,
                HubDeliveryZonePanel::class,
                CatalogOverviewWidget::class,
                HubProductsTable::class,
                HubCategoriesTable::class,
                HubLayoutTable::class,
                HubTagsTable::class,
                HubCompanyProfilePanel::class,
                HubCompanyLegalPanel::class,
                HubDocumentsTable::class,
                HubStaffTable::class,
                HubBannersTable::class,
                HubPromotionsTable::class,
                HubOverviewPanel::class,
                HubFinancePanel::class,
                HubClientsPanel::class,
                HubOrdersPanel::class,
                HubStorefrontPanel::class,
                OverviewRevenueStatsWidget::class,
                OverviewPipelineStatsWidget::class,
                FinanceRevenueStatsWidget::class,
                ClientsKpiStatsWidget::class,
                OrdersPipelineStatsWidget::class,
                ChannelStatsWidget::class,
                ShoppingFunnelStatsWidget::class,
                RevenueTrendChartWidget::class,
                OrdersCountChartWidget::class,
                DeliveryMixChartWidget::class,
                PaymentMixChartWidget::class,
                TopProductsTableWidget::class,
                TopClientsTableWidget::class,
                RecentOrdersTableWidget::class,
            ])
            ->assets([
                Js::make(
                    'delivery-zone-iframe-bridge',
                    asset('js/filament/delivery-zone-iframe-bridge.js'),
                ),
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
            ]);
    }
}
