# Order — маршрутизация

Файл: `routes/api.php`.

## API

```php
Route::middleware('auth.client')->group(function (): void {
    Route::get('order', [OrderController::class, 'index']);
});
```

| Method | URI | Имя (Laravel auto) | Middleware |
|--------|-----|----------------------|------------|
| GET | `/api/order` | — | `api`, `auth.client` |

Связанный write-маршрут (Checkout BC):

| POST | `/api/checkout/{checkoutId}/confirm` | публичный | создаёт Order через event |

## Filament

| URL | Страница |
|-----|----------|
| `/admin/orders` | `ListOrders` |
| `/admin/orders/{id}?tab={ключ}` | `ViewOrder` |

Регистрация: `AdminPanelProvider` → `OrderResource::class`.

## Provider

`config/app.php` → `OrderServiceProvider::class` (DI + event listener).
