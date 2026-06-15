# Order — маршрутизация

Файл: `routes/api.php`.

## API (клиент)

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

## API (создание)

| Method | URI | BC | Middleware |
|--------|-----|-----|------------|
| POST | `/api/checkout/{checkoutId}/confirm` | Checkout → Order | `api` |
| POST | `/api/ingress/{partner}/orders` | AggregatorIngress → Order | `api` |

Ingress: [aggregator-ingress/routing.md](../aggregator-ingress/routing.md).

## Filament

| URL | Страница |
|-----|----------|
| `/admin/orders` | `ListOrders` |
| `/admin/orders/{id}?tab={ключ}` | `ViewOrder` |

Регистрация: `AdminPanelProvider` → `OrderResource::class`.

## Provider

`config/app.php` → `OrderServiceProvider::class` (DI + event listener).
