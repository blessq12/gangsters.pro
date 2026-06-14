# Order — слой домена

Ядро BC без Laravel/HTTP.

## Агрегат

| Элемент | Смысл |
|---------|--------|
| `Order` | Aggregate Root: id?, checkoutId, status, cart, client, delivery, payment, createdAt |

### Фабрики

- `Order::fromCheckoutSnapshot(...)` — создание из слепков (инварианты: непустая корзина, checkout_id).
- `Order::restore(...)` — гидратация из `ORD_orders`.

Агрегат **immutable** после создания — мутаций статуса в домене пока нет.

## Value Objects

| VO | Смысл |
|----|--------|
| `OrderId` | int PK |
| `OrderCartSnapshot` / `OrderLineSnapshot` | позиции + `lineTotal()` |
| `OrderClientSnapshot` | guest \| registered |
| `OrderGuestContact` | name, phone, email? |
| `OrderDeliverySnapshot` / `OrderDeliveryAddress` | доставка |
| `OrderPaymentSnapshot` | оплата |

## Enums

| Enum | Значения |
|------|----------|
| `OrderStatus` | `new`, `preparing`, `in_transit`, `delivered` |
| `OrderClientKind` | `guest`, `registered` |
| `OrderDeliveryMethod` | `courier`, `pickup` |
| `OrderPaymentMethod` | `cash`, `card_courier`, `card_online` |

## Repository (port)

| Метод | Назначение |
|-------|------------|
| `findById` | по PK |
| `findByCheckoutId` | идемпотентность create |
| `existsByCheckoutId` | проверка дубликата |
| `listByClientId` | история клиента, `created_at desc` |
| `save` | insert (assignId после create) |

## Exception

`OrderInvariantViolation` — пустая корзина, пустой checkout_id.

## Зависимости

Order **слушает** Checkout через application listener, не импортирует Checkout Domain в Entity.
