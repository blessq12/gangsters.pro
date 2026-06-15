# Order — события

## Исходящие

| Событие | Когда | Подписчики |
|---------|--------|------------|
| `OrderCreated` | после первого `save` нового заказа | [OrderAccountingExport](../order-accounting-export/events.md) → `OnOrderCreated` |

Класс: `App\Domain\Order\Event\OrderCreated`.

Диспатч в:

- `CreateOrderUseCase` (сайт, через `PlaceOrderUseCase`)
- `CreateOrderFromIngressUseCase` (агрегатор)

**Не** диспатчится при идемпотентном повторе.

Payload: `orderId`, `source`, `checkoutId` (client_request_id для site), cart, client, delivery, payment, `occurredAt`.

## Входящие

| Событие | Источник | Обработчик |
|---------|----------|------------|
| — | — | ~~`CheckoutConfirmed`~~ **удалён** |

Сайт больше не использует промежуточное доменное событие: `PlaceOrderUseCase` вызывает `CreateOrderUseCase` напрямую.

## SPA (не домен Order)

| Событие | Когда |
|---------|--------|
| `order.created` | После успешного `POST /api/orders` |

Trigger: `useCheckoutWizard.handleConfirmOrder` → `useSessionLifecycleProcess` → clear draft + cart.

См. [spa.md](spa.md).

## План

| Событие | Когда |
|---------|--------|
| `OrderStatusChanged` | при реализации смены статуса |
