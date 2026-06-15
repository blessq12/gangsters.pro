# Order — Infrastructure

## Таблица `ORD_orders`

| Колонка | Тип | Смысл |
|---------|-----|--------|
| `id` | bigint PK | |
| `source` | string(32) | `site` \| `aggregator` |
| `checkout_id` | uuid nullable, unique | `client_request_id` для site |
| `partner_code` | string nullable | код агрегатора |
| `external_order_id` | string nullable | ID заказа у партнёра |
| `status` | string(32) | `OrderStatus` |
| `client_id` | bigint nullable, index | для `listByClientId` |
| `total_rubles` | unsigned int | сумма корзины |
| `cart_snapshot` | json | lines + `line_total_rubles` |
| `client_snapshot` | json | |
| `delivery_snapshot` | json | |
| `payment_snapshot` | json | |
| `created_at` | timestamp | без `updated_at` |

Unique: `(partner_code, external_order_id)` для aggregator.

Таблица `CHK_checkouts` **удалена**.

## OrderDraft ports (Infrastructure)

`app/Infrastructure/Order/Port/`:

- `CatalogPricingAdapter`, `CatalogGiftCandidatesAdapter`, …
- `ClientProfileAdapter`

## Модель

`App\Infrastructure\Order\Model\ORD_Order` — Eloquent, `$timestamps = false`.

## Mapper

`OrderMapper` — domain ↔ persistence.

## Repository

`EloquentOrderRepository` implements `OrderRepository`.

`findByClientRequestId` — alias `findByCheckoutId`.

## Provider

`OrderServiceProvider`:

- bind `OrderRepository` + OrderDraft ports
- `Event::listen(OrderCreated, OnOrderCreated)` → OrderAccountingExport

Ingress-таблицы — [AggregatorIngress infrastructure](../aggregator-ingress/infrastructure.md).
