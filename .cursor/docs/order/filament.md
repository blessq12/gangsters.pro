# Order — Filament (оператор)

Read-only: оператор смотрит заказы из `ORD_orders`. Создание/редактирование запрещены.

## Навигация

**«Заказы»** — `OrderResource`, `navigationSort = 27`, иконка `ShoppingBag`.

## Ресурс

| Класс | Модель | Slug |
|-------|--------|------|
| `OrderResource` | `ORD_Order` | `orders` |

Страницы: `index` + `view` (нет create/edit).

## Список (`OrdersTable`)

| Колонка | Источник |
|---------|----------|
| ID | `id` |
| Источник | `source` — «Сайт» / «Агрегатор» |
| Статус | badge + `OrderSnapshotReader::statusLabel()` |
| Сумма | `total_rubles` |
| Клиент | `client_snapshot` |
| Доставка / Оплата | JSON-слепки |
| Checkout | `checkout_id` (copyable), «—» для aggregator |
| Партнёр | `partner_code` → `OrderSnapshotReader::partnerLabel()` |
| Внешний ID | `external_order_id` |
| Создан | `created_at` |

Фильтр по `status`. Сортировка: `created_at desc`.

## Просмотр (`ViewOrder`)

`mutateFormDataBeforeFill()` → `OrderSnapshotReader::formDataFromRecord()`.

Табы (`OrderViewSchema`, ключи ассоциативные + `activeOrderViewTab`):

| `?tab=` | Блок |
|---------|------|
| `overview` | id, source, checkout_id, partner, external_order_id, status, created_at |
| `cart` | сумма + `RepeatableEntry` table (TextEntry) |
| `client` | client snapshot |
| `delivery` | delivery snapshot |
| `payment` | payment snapshot |

## Support

`app/Filament/Order/Support/OrderSnapshotReader.php` — русские подписи enum, формат денег.

Filament не использует Domain Order — только Eloquent + JSON (как Checkout).

## Принцип

| Контур | Read | Write |
|--------|------|-------|
| Filament | список + просмотр | **запрещён** |
| API клиента | `GET /api/order` | через Checkout confirm |
| Ingress API | — | [AggregatorIngress](../aggregator-ingress/overview.md) |
| SPA | история в профиле (только site + registered) | checkout wizard |
