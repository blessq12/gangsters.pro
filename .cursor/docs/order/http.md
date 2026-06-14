# Order — HTTP-слой

Тонкий адаптер: Request → DTO → use case → JSON.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\OrderController`

| Действие | HTTP | Auth | Use case |
|----------|------|------|----------|
| `index()` | `GET /api/order` | `auth.client` | `ListClientOrdersUseCase` |

Ответ **200**:

```json
{
  "data": [ /* OrderPresenter[] */ ]
}
```

Авторизация: middleware `AuthenticateClient` (Bearer `CLN_Client`, без web-сессии Filament).

## Создание заказа

Write **не** через `/api/order`. Создание:

`POST /api/checkout/{checkoutId}/confirm` → `CheckoutConfirmed` → `CreateOrderUseCase`.

В ответе confirm присутствует поле `order` (тот же `OrderPresenter`).

## Legacy

| Артефакт | Статус |
|----------|--------|
| `StoreOrderRequest` | не подключён к роутам |
| `POST /api/order` в `orderApi.js` | legacy; SPA использует checkout confirm |

## Handler

`OrderInvariantViolation` → 422 JSON на `api/*`.

## SPA

`resources/js/api/orderApi.js` — `fetchOrdersRequest()` → `GET /api/order`.

Store: `orderStore.fetchOrders()` ожидает `data.data` или массив в корне.
