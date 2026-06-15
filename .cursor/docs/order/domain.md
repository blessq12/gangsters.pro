# Order — слой домена

Ядро BC без Laravel/HTTP.

## Агрегат

| Элемент | Смысл |
|---------|--------|
| `Order` | Aggregate Root: id?, source, checkoutId?, aggregatorRef?, status, cart, client, delivery, payment, createdAt |

### Фабрики

- `Order::fromCheckoutSnapshot(...)` — сайт; инварианты: непустая корзина, `checkout_id`.
- `Order::fromIngressSnapshot(...)` — агрегатор; инварианты: partner + external_order_id, непустая корзина.
- `Order::restore(...)` — гидратация из `ORD_orders`.

Агрегат **immutable** после создания — мутаций статуса в домене пока нет.

## Value Objects

| VO | Смысл |
|----|--------|
| `OrderId` | int PK |
| `OrderAggregatorReference` | partnerCode + externalOrderId |
| `OrderCartSnapshot` / `OrderLineSnapshot` | позиции + `lineTotal()` |
| `OrderClientSnapshot` | guest \| registered |
| `OrderGuestContact` | name, phone, email? |
| `OrderDeliverySnapshot` / `OrderDeliveryAddress` | доставка |
| `OrderPaymentSnapshot` | оплата |

## Enums

| Enum | Значения |
|------|----------|
| `OrderSource` | `site`, `aggregator` |
| `OrderStatus` | `new`, `preparing`, `in_transit`, `delivered` |
| `OrderClientKind` | `guest`, `registered` |
| `OrderDeliveryMethod` | `courier`, `pickup` |
| `OrderPaymentMethod` | `cash`, `card_courier`, `card_online` |

## Repository (port)

| Метод | Назначение |
|-------|------------|
| `findById` | по PK |
| `findByCheckoutId` | идемпотентность site create |
| `findByPartnerAndExternalOrderId` | идемпотентность aggregator create |
| `existsByCheckoutId` | проверка дубликата |
| `listByClientId` | история клиента, `created_at desc` |
| `save` | insert (assignId после create) |

## Exception

`OrderInvariantViolation` — пустая корзина, пустой checkout_id, пустая ссылка агрегатора.

## Зависимости

- Order **слушает** Checkout через `OnCheckoutConfirmed`.
- Order **не импортирует** AggregatorIngress; ingress вызывает `CreateOrderFromIngressUseCase` из Application.
