<?php

use App\Http\Controllers\Api\Backoffice\AuthController;
use App\Http\Controllers\Api\Backoffice\CatalogController;
use App\Http\Controllers\Api\Backoffice\ClientController;
use App\Http\Controllers\Api\Backoffice\CompanyController;
use App\Http\Controllers\Api\Backoffice\CourierController;
use App\Http\Controllers\Api\Backoffice\DeliveryController;
use App\Http\Controllers\Api\Backoffice\EmployeeController;
use App\Http\Controllers\Api\Backoffice\MarketingController;
use App\Http\Controllers\Api\Backoffice\OrderController;
use App\Http\Controllers\Api\Backoffice\PaymentController;
use App\Http\Controllers\Api\Backoffice\ProductionController;
use App\Http\Controllers\Api\Backoffice\PromotionController;
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
            Route::post('employees', [EmployeeController::class, 'store']);
            Route::put('employees/{id}', [EmployeeController::class, 'update'])->whereNumber('id');
            Route::delete('employees/{id}', [EmployeeController::class, 'destroy'])->whereNumber('id');
            Route::post('employees/{id}/toggle-active', [EmployeeController::class, 'toggleActive'])
                ->whereNumber('id');

            Route::get('clients', [ClientController::class, 'index']);
            Route::get('clients/{id}', [ClientController::class, 'show'])->whereNumber('id');

            Route::get('orders', [OrderController::class, 'index']);
            Route::post('orders', [OrderController::class, 'store']);
            Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
            Route::post('orders/{id}/accept', [OrderController::class, 'accept'])->whereNumber('id');
            Route::post('orders/{id}/reassign-kitchen', [OrderController::class, 'reassignKitchen'])
                ->whereNumber('id');
            Route::post('orders/{id}/status', [OrderController::class, 'changeStatus'])->whereNumber('id');
            Route::post('orders/{id}/complete-pickup', [OrderController::class, 'completePickup'])
                ->whereNumber('id');

            Route::get('production/queue', [ProductionController::class, 'queue']);
            Route::get('production/tickets/{orderId}', [ProductionController::class, 'show'])
                ->whereNumber('orderId');
            Route::post('production/tickets/{orderId}/queue', [ProductionController::class, 'enqueue'])
                ->whereNumber('orderId');
            Route::post('production/tickets/{orderId}/start', [ProductionController::class, 'start'])
                ->whereNumber('orderId');
            Route::post('production/tickets/{orderId}/complete', [ProductionController::class, 'complete'])
                ->whereNumber('orderId');
            Route::post('production/tickets/{orderId}/cancel', [ProductionController::class, 'cancel'])
                ->whereNumber('orderId');

            Route::get('payments/{id}', [PaymentController::class, 'show'])
                ->whereNumber('id');
            Route::post('payments/{id}/paid', [PaymentController::class, 'markPaid'])
                ->whereNumber('id');
            Route::post('payments/{id}/refund', [PaymentController::class, 'refund'])
                ->whereNumber('id');

            Route::get('catalog', [CatalogController::class, 'show']);

            Route::post('catalog/products', [CatalogController::class, 'storeProduct']);
            Route::put('catalog/products/{id}', [CatalogController::class, 'updateProduct'])
                ->whereNumber('id');

            Route::post('catalog/categories', [CatalogController::class, 'storeCategory']);
            Route::put('catalog/categories/{id}', [CatalogController::class, 'updateCategory'])
                ->whereNumber('id');

            Route::post('catalog/sets', [CatalogController::class, 'storeSet']);
            Route::put('catalog/sets/{id}', [CatalogController::class, 'updateSet'])
                ->whereNumber('id');

            Route::post('catalog/tags', [CatalogController::class, 'storeTag']);
            Route::put('catalog/tags/{id}', [CatalogController::class, 'updateTag'])
                ->whereNumber('id');

            Route::get('company', [CompanyController::class, 'show']);
            Route::post('company', [CompanyController::class, 'store']);
            Route::put('company', [CompanyController::class, 'update']);

            Route::post('company/branches', [CompanyController::class, 'storeBranch']);
            Route::put('company/branches/{id}', [CompanyController::class, 'updateBranch'])
                ->whereNumber('id');

            Route::post('company/kitchens', [CompanyController::class, 'storeKitchen']);
            Route::put('company/kitchens/{id}', [CompanyController::class, 'updateKitchen'])
                ->whereNumber('id');

            Route::post('company/documents', [CompanyController::class, 'storeDocument']);
            Route::put('company/documents/{id}', [CompanyController::class, 'updateDocument'])
                ->whereNumber('id');

            Route::get('delivery', [DeliveryController::class, 'show']);
            Route::post('delivery', [DeliveryController::class, 'store']);
            Route::put('delivery', [DeliveryController::class, 'update']);

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

            Route::get('promotion-policy', [PromotionController::class, 'show']);
            Route::post('promotion-policy', [PromotionController::class, 'store']);
            Route::put('promotion-policy', [PromotionController::class, 'update']);
            Route::post('promotion-policy/roles', [PromotionController::class, 'assignRole']);
            Route::post('promotion-policy/sync-zones', [PromotionController::class, 'syncZones']);

            Route::get('marketing', [MarketingController::class, 'show']);
            Route::post('marketing/banners', [MarketingController::class, 'storeBanner']);
            Route::put('marketing/banners/{id}', [MarketingController::class, 'updateBanner'])
                ->whereNumber('id');
            Route::post('marketing/promotions', [MarketingController::class, 'storePromotion']);
            Route::put('marketing/promotions/{id}', [MarketingController::class, 'updatePromotion'])
                ->whereNumber('id');
            Route::post('marketing/delivery-conditions', [MarketingController::class, 'saveDeliveryConditions']);
            Route::put('marketing/delivery-conditions', [MarketingController::class, 'saveDeliveryConditions']);

            Route::get('warehouse/ingredients', [WarehouseController::class, 'ingredients']);
            Route::post('warehouse/ingredients', [WarehouseController::class, 'storeIngredient']);
            Route::put('warehouse/ingredients/{id}', [WarehouseController::class, 'updateIngredient'])
                ->whereNumber('id');
            Route::get('warehouse/stock', [WarehouseController::class, 'stock']);
            Route::post('warehouse/stock/adjust', [WarehouseController::class, 'adjustStock']);
            Route::get('warehouse/write-offs', [WarehouseController::class, 'writeOffs']);
            Route::post('warehouse/write-offs', [WarehouseController::class, 'storeWriteOff']);
        });
    });
});
