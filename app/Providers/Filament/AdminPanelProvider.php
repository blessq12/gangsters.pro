<?php

namespace App\Providers\Filament;

use App\Filament\Catalog\Pages\ManageCatalog;
use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Resources\TagResource;
use App\Filament\Operations\Pages\ManageCartRuleSettings;
use App\Filament\Operations\Pages\ManageDeliveryZone;
use App\Filament\Operations\Pages\ManageOperations;
use App\Filament\Operations\Resources\ClientResource;
use App\Filament\Operations\Resources\OrderResource;
use App\Filament\Pages\AdminDashboard;
use App\Http\Controllers\Admin\DeliveryZoneMapEditorController;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
                AdminDashboard::class,
                ManageCatalog::class,
                ManageOperations::class,
                ManageDeliveryZone::class,
                ManageCartRuleSettings::class,
            ])
            ->resources([
                ProductResource::class,
                CategoryResource::class,
                TagResource::class,
                OrderResource::class,
                ClientResource::class,
            ])
            ->routes(function (): void {
                Route::get('/delivery-zone-map-editor', DeliveryZoneMapEditorController::class)
                    ->name('delivery-zone-map-editor');
            })
            ->widgets([
                AccountWidget::class,
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
