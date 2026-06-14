# Order — Infrastructure

## Таблица `ORD_orders`

| Колонка | Тип | Смысл |
|---------|-----|--------|
| `id` | bigint PK | |
| `checkout_id` | uuid unique | ссылка на чекаут |
| `status` | string(32) | `OrderStatus` |
| `client_id` | bigint nullable, index | для `listByClientId` |
| `total_rubles` | unsigned int | сумма корзины |
| `cart_snapshot` | json | lines + `line_total_rubles` |
| `client_snapshot` | json | |
| `delivery_snapshot` | json | |
| `payment_snapshot` | json | |
| `created_at` | timestamp | без `updated_at` |

Миграция: `database/migrations/2026_06_14_190000_create_ord_orders_table.php`.

## Модель

`App\Infrastructure\Order\Model\ORD_Order` — Eloquent, `$timestamps = false`.

## Mapper

`OrderMapper`:

- `toDomain(ORD_Order)` → `Order::restore`
- `toPersistence(Order)` → массив для insert/update
- serialize cart lines с `line_total_rubles`

## Repository

`EloquentOrderRepository` implements `OrderRepository`.

## Provider

`OrderServiceProvider`:

- bind `OrderRepository`
- `Event::listen(CheckoutConfirmed, OnCheckoutConfirmed)`
