# Order — Infrastructure

## Таблица `ORD_orders`

| Колонка | Тип | Смысл |
|---------|-----|--------|
| `id` | bigint PK | |
| `source` | string(32) | `site` \| `aggregator` |
| `checkout_id` | uuid nullable, unique | ссылка на чекаут (site) |
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

Миграции:

- `database/migrations/2026_06_14_190000_create_ord_orders_table.php`
- `database/migrations/2026_06_15_100000_extend_ord_orders_for_aggregator_ingress.php`

## Модель

`App\Infrastructure\Order\Model\ORD_Order` — Eloquent, `$timestamps = false`.

## Mapper

`OrderMapper`:

- `toDomain(ORD_Order)` → `Order::restore`
- `toPersistence(Order)` → массив для insert/update
- serialize cart lines с `line_total_rubles`

## Repository

`EloquentOrderRepository` implements `OrderRepository`.

Метод `findByPartnerAndExternalOrderId` — для ingress идемпотентности.

## Provider

`OrderServiceProvider`:

- bind `OrderRepository`
- `Event::listen(CheckoutConfirmed, OnCheckoutConfirmed)`

Ingress-таблицы — см. [AggregatorIngress infrastructure](../aggregator-ingress/infrastructure.md).
