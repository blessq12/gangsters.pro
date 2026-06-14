# Checkout — роутинг

## Публичное API

| Метод | Путь | Controller@action |
|-------|------|-------------------|
| `POST` | `/api/checkout` | `CheckoutController@store` |
| `PATCH` | `/api/checkout/{checkoutId}/cart` | `CheckoutController@updateCart` |
| `PATCH` | `/api/checkout/{checkoutId}/client` | `CheckoutController@setClient` |
| `PATCH` | `/api/checkout/{checkoutId}/delivery` | `CheckoutController@setDelivery` |
| `PATCH` | `/api/checkout/{checkoutId}/payment` | `CheckoutController@setPayment` |
| `POST` | `/api/checkout/{checkoutId}/confirm` | `CheckoutController@confirm` |

Регистрация: `routes/api.php` → группа `api` (префикс `/api`), блок `Route::prefix('checkout')`.

`{checkoutId}` — UUID строка без доп. regex constraint.

## Чего нет

- `GET /api/checkout/{id}` — read черновика.
- `/api/shopping/*` — legacy контракт фронта (не зарегистрирован).
- Filament / admin маршруты для checkout.
- Rate limit отдельно от общего `api` throttle (60/min).
