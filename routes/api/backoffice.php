<?php

use App\Http\Controllers\Api\Backoffice\AuthController;
use App\Http\Controllers\Api\Backoffice\BranchMenuController;
use App\Http\Controllers\Api\Backoffice\BranchMenuStructureController;
use App\Http\Controllers\Api\Backoffice\CatalogController;
use App\Http\Controllers\Api\Backoffice\ClientController;
use App\Http\Controllers\Api\Backoffice\CompanyController;
use App\Http\Controllers\Api\Backoffice\CourierController;
use App\Http\Controllers\Api\Backoffice\DashboardController;
use App\Http\Controllers\Api\Backoffice\DeliveryController;
use App\Http\Controllers\Api\Backoffice\EmployeeController;
use App\Http\Controllers\Api\Backoffice\MarketingController;
use App\Http\Controllers\Api\Backoffice\OrderController;
use App\Http\Controllers\Api\Backoffice\PaymentController;
use App\Http\Controllers\Api\Backoffice\ProductionController;
use App\Http\Controllers\Api\Backoffice\WarehouseController;
use App\Http\Middleware\EnsureBackofficeEnabled;
use Illuminate\Support\Facades\Route;

/*
| Backoffice API: auth + role guard + employees/orders/production/catalog/...
*/

Route::prefix('backoffice')->middleware(EnsureBackofficeEnabled::class)->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::middleware('backoffice.auth')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('ping', static function () {
            return response()->json(['ok' => true]);
        });

        Route::middleware('backoffice.role')->group(function (): void {
            Route::get('employees', [EmployeeController::class, 'index']);
            Route::get('employees/{id}', [EmployeeController::class, 'show'])->whereNumber('id');
            Route::post('employees', [EmployeeController::class, 'store']);
            Route::put('employees/{id}', [EmployeeController::class, 'update'])->whereNumber('id');
            Route::post('employees/{id}/toggle-active', [EmployeeController::class, 'toggleActive'])
                ->whereNumber('id');

            Route::get('clients', [ClientController::class, 'index']);
            Route::post('clients/ensure', [ClientController::class, 'ensure']);
            Route::get('clients/{id}', [ClientController::class, 'show'])->whereNumber('id');
            Route::patch('clients/{id}', [ClientController::class, 'updateProfile'])->whereNumber('id');
            Route::post('clients/{id}/addresses', [ClientController::class, 'addAddress'])->whereNumber('id');
            Route::put('clients/{id}/addresses/{addressId}', [ClientController::class, 'updateAddress'])
                ->whereNumber('id');
            Route::delete('clients/{id}/addresses/{addressId}', [ClientController::class, 'deleteAddress'])
                ->whereNumber('id');
            Route::post('clients/{id}/addresses/{addressId}/default', [ClientController::class, 'setDefaultAddress'])
                ->whereNumber('id');

            Route::get('orders', [OrderController::class, 'index']);
            Route::post('orders/quote', [OrderController::class, 'quote']);
            Route::post('orders', [OrderController::class, 'store']);
            Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
            Route::put('orders/{id}', [OrderController::class, 'update'])->whereNumber('id');
            Route::post('orders/{id}/accept', [OrderController::class, 'accept'])->whereNumber('id');
            Route::post('orders/{id}/status', [OrderController::class, 'changeStatus'])->whereNumber('id');
            Route::post('orders/{id}/complete-pickup', [OrderController::class, 'completePickup'])
                ->whereNumber('id');
            Route::post('orders/{id}/notes', [OrderController::class, 'addNote'])->whereNumber('id');

            Route::get('production/queue', [ProductionController::class, 'queue']);
            Route::get('production/tickets/{orderId}', [ProductionController::class, 'show'])
                ->whereNumber('orderId');
            Route::post('production/tickets/{orderId}/start', [ProductionController::class, 'start'])
                ->whereNumber('orderId');
            Route::post('production/tickets/{orderId}/complete', [ProductionController::class, 'complete'])
                ->whereNumber('orderId');
            Route::post('production/tickets/{orderId}/lines/{lineId}/done', [ProductionController::class, 'markLineDone'])
                ->whereNumber('orderId')
                ->whereNumber('lineId');
            Route::post('production/tickets/{orderId}/lines/{lineId}/pack', [ProductionController::class, 'markLinePacked'])
                ->whereNumber('orderId')
                ->whereNumber('lineId');
            Route::post('production/tickets/{orderId}/cancel', [ProductionController::class, 'cancel'])
                ->whereNumber('orderId');

            Route::get('dashboard/summary', [DashboardController::class, 'summary']);

            Route::get('payments', [PaymentController::class, 'index']);
            Route::get('payments/summary', [PaymentController::class, 'summary']);
            Route::get('payments/{id}', [PaymentController::class, 'show'])
                ->whereNumber('id');
            Route::post('payments/{id}/paid', [PaymentController::class, 'markPaid'])
                ->whereNumber('id');
            Route::post('payments/{id}/refund', [PaymentController::class, 'refund'])
                ->whereNumber('id');

            Route::get('catalog', [CatalogController::class, 'show']);
            Route::get('catalog/products', [CatalogController::class, 'listProducts']);
            Route::get('catalog/products/{id}', [CatalogController::class, 'showProduct'])
                ->whereNumber('id');

            Route::post('catalog/products', [CatalogController::class, 'storeProduct']);
            Route::put('catalog/products/{id}', [CatalogController::class, 'updateProduct'])
                ->whereNumber('id');
            Route::delete('catalog/products/{id}', [CatalogController::class, 'destroyProduct'])
                ->whereNumber('id');

            Route::post('catalog/items/{id}/images', [CatalogController::class, 'storeItemImage'])
                ->whereNumber('id');
            Route::patch('catalog/items/{id}/images/{imageId}', [CatalogController::class, 'updateItemImage'])
                ->whereNumber('id')
                ->whereNumber('imageId');
            Route::delete('catalog/items/{id}/images/{imageId}', [CatalogController::class, 'destroyItemImage'])
                ->whereNumber('id')
                ->whereNumber('imageId');

            Route::get('company', [CompanyController::class, 'show']);
            Route::post('company', [CompanyController::class, 'store']);
            Route::put('company', [CompanyController::class, 'update']);

            Route::get('company/branches', [CompanyController::class, 'listBranches']);
            Route::get('company/branches/{id}', [CompanyController::class, 'showBranch'])
                ->whereNumber('id');
            Route::post('company/branches', [CompanyController::class, 'storeBranch']);
            Route::put('company/branches/{id}', [CompanyController::class, 'updateBranch'])
                ->whereNumber('id');

            Route::post('company/branches/clone-settings', [BranchMenuController::class, 'cloneSettings']);
            Route::get('company/branches/{branchId}/menu', [BranchMenuController::class, 'index'])
                ->whereNumber('branchId');
            Route::put('company/branches/{branchId}/menu/{catalogItemId}', [BranchMenuController::class, 'upsert'])
                ->whereNumber('branchId')
                ->whereNumber('catalogItemId');
            Route::delete('company/branches/{branchId}/menu/{catalogItemId}', [BranchMenuController::class, 'destroy'])
                ->whereNumber('branchId')
                ->whereNumber('catalogItemId');

            Route::prefix('company/branches/{branchId}')->whereNumber('branchId')->group(function (): void {
                Route::get('categories', [BranchMenuStructureController::class, 'indexCategories']);
                Route::post('categories', [BranchMenuStructureController::class, 'storeCategory']);
                Route::put('categories/order', [BranchMenuStructureController::class, 'reorderCategories']);
                Route::get('categories/{id}', [BranchMenuStructureController::class, 'showCategory'])
                    ->whereNumber('id');
                Route::put('categories/{id}', [BranchMenuStructureController::class, 'updateCategory'])
                    ->whereNumber('id');
                Route::delete('categories/{id}', [BranchMenuStructureController::class, 'destroyCategory'])
                    ->whereNumber('id');
                Route::put('categories/{id}/items', [BranchMenuStructureController::class, 'replaceCategoryItems'])
                    ->whereNumber('id');

                Route::get('tags', [BranchMenuStructureController::class, 'indexTags']);
                Route::post('tags', [BranchMenuStructureController::class, 'storeTag']);
                Route::put('tags/{id}', [BranchMenuStructureController::class, 'updateTag'])
                    ->whereNumber('id');
                Route::delete('tags/{id}', [BranchMenuStructureController::class, 'destroyTag'])
                    ->whereNumber('id');

                Route::get('sets', [BranchMenuStructureController::class, 'indexSets']);
                Route::post('sets', [BranchMenuStructureController::class, 'storeSet']);
                Route::get('sets/{id}', [BranchMenuStructureController::class, 'showSet'])
                    ->whereNumber('id');
                Route::put('sets/{id}', [BranchMenuStructureController::class, 'updateSet'])
                    ->whereNumber('id');
                Route::delete('sets/{id}', [BranchMenuStructureController::class, 'destroySet'])
                    ->whereNumber('id');
            });

            Route::get('company/documents', [CompanyController::class, 'listDocuments']);
            Route::get('company/documents/{id}', [CompanyController::class, 'showDocument'])
                ->whereNumber('id');
            Route::post('company/documents', [CompanyController::class, 'storeDocument']);
            Route::put('company/documents/{id}', [CompanyController::class, 'updateDocument'])
                ->whereNumber('id');

            Route::get('delivery', [DeliveryController::class, 'show']);
            Route::post('delivery', [DeliveryController::class, 'store']);
            Route::put('delivery', [DeliveryController::class, 'update']);

            Route::get('delivery/zones', [DeliveryController::class, 'listZones']);
            Route::put('delivery/zones', [DeliveryController::class, 'replaceZones']);
            Route::post('delivery/zones', [DeliveryController::class, 'storeZone']);
            Route::put('delivery/zones/{id}', [DeliveryController::class, 'updateZone']);

            Route::get('delivery/route-sheets', [DeliveryController::class, 'listRouteSheets']);
            Route::post('delivery/route-sheets', [DeliveryController::class, 'createRouteSheet']);
            Route::get('delivery/route-sheets/{id}', [DeliveryController::class, 'showRouteSheet'])
                ->whereNumber('id');
            Route::post('delivery/route-sheets/{id}/take', [DeliveryController::class, 'takeRouteSheet'])
                ->whereNumber('id');
            Route::post(
                'delivery/route-sheets/{id}/stops/{orderId}/settle',
                [DeliveryController::class, 'settleStop'],
            )->whereNumber('id')->whereNumber('orderId');

            Route::get('courier/route-sheets', [CourierController::class, 'routeSheets']);

            Route::get('order/commercial-rules', [OrderController::class, 'showCommercialRules']);
            Route::post('order/commercial-rules', [OrderController::class, 'storeCommercialRules']);
            Route::put('order/commercial-rules', [OrderController::class, 'updateCommercialRules']);

            Route::get('marketing', [MarketingController::class, 'show']);
            Route::get('marketing/banners', [MarketingController::class, 'listBanners']);
            Route::get('marketing/banners/{id}', [MarketingController::class, 'showBanner'])
                ->whereNumber('id');
            Route::post('marketing/banners', [MarketingController::class, 'storeBanner']);
            Route::put('marketing/banners/{id}', [MarketingController::class, 'updateBanner'])
                ->whereNumber('id');
            Route::post('marketing/banners/{id}', [MarketingController::class, 'updateBanner'])
                ->whereNumber('id');
            Route::get('marketing/promotions', [MarketingController::class, 'listPromotions']);
            Route::get('marketing/promotions/{id}', [MarketingController::class, 'showPromotion'])
                ->whereNumber('id');
            Route::post('marketing/promotions', [MarketingController::class, 'storePromotion']);
            Route::put('marketing/promotions/{id}', [MarketingController::class, 'updatePromotion'])
                ->whereNumber('id');
            Route::post('marketing/promotions/{id}', [MarketingController::class, 'updatePromotion'])
                ->whereNumber('id');
            Route::post('marketing/delivery-conditions', [MarketingController::class, 'saveDeliveryConditions']);
            Route::put('marketing/delivery-conditions', [MarketingController::class, 'saveDeliveryConditions']);

            Route::get('warehouse/ingredients', [WarehouseController::class, 'ingredients']);
            Route::post('warehouse/ingredients', [WarehouseController::class, 'storeIngredient']);
            Route::get('warehouse/ingredients/{id}', [WarehouseController::class, 'showIngredient'])
                ->whereNumber('id');
            Route::put('warehouse/ingredients/{id}', [WarehouseController::class, 'updateIngredient'])
                ->whereNumber('id');

            Route::get('warehouse/branches/{branchId}/stock', [WarehouseController::class, 'branchStock'])
                ->whereNumber('branchId');
            Route::post('warehouse/branches/{branchId}/stock/adjust', [WarehouseController::class, 'adjustBranchStock'])
                ->whereNumber('branchId');
            Route::get('warehouse/branches/{branchId}/ingredients/{id}', [WarehouseController::class, 'showBranchIngredient'])
                ->whereNumber('branchId')
                ->whereNumber('id');
            Route::get('warehouse/branches/{branchId}/write-offs', [WarehouseController::class, 'branchWriteOffs'])
                ->whereNumber('branchId');
            Route::post('warehouse/branches/{branchId}/write-offs', [WarehouseController::class, 'storeBranchWriteOff'])
                ->whereNumber('branchId');
        });
    });
});
