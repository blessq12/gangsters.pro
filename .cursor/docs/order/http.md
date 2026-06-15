# Order — HTTP-слой

Тонкий адаптер: Request → DTO → use case → JSON.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\OrderController`

| Действие | HTTP | Auth | Use case |
|----------|------|------|----------|
| `index()` | `GET /api/order` | `auth.client` | `ListClientOrdersUseCase` |
| `show()` | `GET /api/order/{orderId}` | `auth.client` | `GetOrderUseCase` |

Ответ **200**:

```json
{
  "data": [ /* OrderPresenter[] */ ]
}
```

Авторизация: middleware `AuthenticateClient` (Bearer `CLN_Client`, без web-сессии Filament).

## Создание заказа

| Канал | Маршрут | Use case |
|-------|---------|----------|
| Сайт | `POST /api/checkout/{checkoutId}/confirm` | `CreateOrderUseCase` (через event) |
| Агрегатор | `POST /api/ingress/{partner}/orders` | `CreateOrderFromIngressUseCase` |

В ответе confirm присутствует поле `order` (тот же `OrderPresenter`).

Агрегаторы: см. [AggregatorIngress http](../aggregator-ingress/http.md).

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
