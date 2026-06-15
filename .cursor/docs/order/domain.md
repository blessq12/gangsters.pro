# Order — слой домена

Ядро BC без Laravel/HTTP.

## Агрегат Order

| Элемент | Смысл |
|---------|--------|
| `Order` | Aggregate Root: id?, source, checkoutId? (client_request_id), aggregatorRef?, status, cart, client, delivery, payment, createdAt |

### Фабрики

- `Order::fromCheckoutSnapshot(...)` — сайт; инварианты: непустая корзина, `checkout_id`.
- `Order::fromIngressSnapshot(...)` — агрегатор; partner + external_order_id.
- `Order::restore(...)` — гидратация из `ORD_orders`.

Агрегат **immutable** после создания.

## OrderDraft (in-memory, не агрегат)

Расположение: `app/Domain/Order/OrderDraft/`.

| Элемент | Смысл |
|---------|--------|
| `OrderDraft` | Черновик: cart, client?, delivery?, payment?; методы `assertReadyForPlace`, `assertValidForPlace` |
| `CartLineSnapshot`, `CartSnapshot`, … | VO черновика (копия бывшего Checkout) |
| `OrderDraftNotReadyForPlace`, … | Исключения валидации |

OrderDraft **не персистится** и не имеет repository.

## Value Objects (Order snapshot)

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
| `findByCheckoutId` / `findByClientRequestId` | идемпотентность site create |
| `findByPartnerAndExternalOrderId` | идемпотентность aggregator |
| `existsByCheckoutId` | проверка дубликата |
| `listByClientId` | история клиента |
| `save` | insert |

## Ports (ACL к другим BC)

`app/Domain/Order/Port/`:

| Port | Назначение |
|------|------------|
| `CatalogPricingPort` | Котировки цен |
| `CatalogGiftCandidatesPort` | Кандидаты подарка |
| `CatalogComplementSetCandidatesPort` | Комplement set |
| `CatalogRollMetaPort` | `counts_as_roll` |
| `ClientProfilePort` | Профиль для registered client |

## Exception

`OrderInvariantViolation` — пустая корзина, пустой checkout_id, пустая ссылка агрегатора.

## Зависимости

- Сайт: `PlaceOrderUseCase` → `CreateOrderUseCase` (без доменного события Checkout).
- Order **не импортирует** AggregatorIngress; ingress вызывает Application use case.
