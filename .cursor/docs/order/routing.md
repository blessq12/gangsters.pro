# Order — маршрутизация

Файл: `routes/api.php`.

## API (OrderDraft + Place)

```php
Route::post('order-drafts/preview', [OrderDraftController::class, 'preview']);
Route::post('orders', [OrderDraftController::class, 'store']);
```

| Method | URI | Middleware |
|--------|-----|------------|
| POST | `/api/order-drafts/preview` | `api` |
| POST | `/api/orders` | `api` |

## API (read, клиент)

```php
Route::middleware('auth.client')->group(function (): void {
    Route::get('order', [OrderController::class, 'index']);
    Route::get('order/{orderId}', [OrderController::class, 'show']);
});
```

| Method | URI | Middleware |
|--------|-----|------------|
| GET | `/api/order` | `api`, `auth.client` |
| GET | `/api/order/{orderId}` | `api`, `auth.client` |

## API (агрегатор)

| Method | URI | BC | Middleware |
|--------|-----|-----|------------|
| POST | `/api/ingress/{partner}/orders` | AggregatorIngress → Order | `api` |

Ingress: [aggregator-ingress/routing.md](../aggregator-ingress/routing.md).

## Storefront (смежный, не Order)

| Method | URI |
|--------|-----|
| GET | `/api/storefront/bootstrap` |

## Filament

| URL | Страница |
|-----|----------|
| `/admin/orders` | `ListOrders` |
| `/admin/orders/{id}?tab={ключ}` | `ViewOrder` |

Регистрация: `AdminPanelProvider` → `OrderResource::class`.

## Provider

`config/app.php` → `OrderServiceProvider::class` (DI OrderDraft ports + `OrderCreated` listener).

## Удалено

~~`POST /api/checkout/{checkoutId}/confirm`~~
