# Order — события

## Исходящие

| Событие | Когда | Подписчики |
|---------|--------|------------|
| `OrderCreated` | после первого `save` нового заказа | [OrderAccountingExport](../order-accounting-export/events.md) → `OnOrderCreated` |

Класс: `App\Domain\Order\Event\OrderCreated`.

Диспатч в:

- `CreateOrderUseCase` (сайт)
- `CreateOrderFromIngressUseCase` (агрегатор)

**Не** диспатчится при идемпотентном `return $existing`.

Payload: полный снимок `orderId`, `source`, `cart`, `client`, `delivery`, `payment`, `occurredAt`.

## Входящие

| Событие | Источник | Обработчик |
|---------|----------|------------|
| `CheckoutConfirmed` | Checkout BC | `OnCheckoutConfirmed` → `CreateOrderUseCase` |

Подписка: `OrderServiceProvider::boot()`.

## Payload `CheckoutConfirmed`

Содержит слепки cart, client, delivery, payment и `checkoutId` — маппится ACL `CheckoutConfirmedOrderSnapshotMapper`.

## План

| Событие | Когда |
|---------|--------|
| `OrderStatusChanged` | при реализации смены статуса |

Другие потенциальные подписчики `OrderCreated`: email/push клиенту, аналитика (пока не подключены).
