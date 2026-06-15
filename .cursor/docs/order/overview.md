# Order — обзор BC

**Роль:** неизменяемый снимок подтверждённого оформления (Checkout) **или** ingress-заказа агрегатора. Хранится в `ORD_orders`.

## Источники заказа

| Источник | `OrderSource` | Ключ уникальности | Use case |
|----------|---------------|-------------------|----------|
| Сайт (Checkout) | `site` | `checkout_id` | `CreateOrderUseCase` |
| Агрегатор | `aggregator` | `partner_code` + `external_order_id` | `CreateOrderFromIngressUseCase` |

Подробнее про агрегаторов: [AggregatorIngress overview](../aggregator-ingress/overview.md).

## Семантика

| Термин | Смысл |
|--------|--------|
| **Order (агрегат)** | Immutable snapshot: cart, client, delivery, payment + статус lifecycle |
| **checkout_id** | UUID ссылки на `CHK_checkouts` (nullable для aggregator) |
| **partner_code / external_order_id** | ссылка на заказ партнёра (nullable для site) |
| **client_id** | Денормализация для списка заказов клиента (nullable для гостя / агрегатора) |
| **Статус** | `new` → `preparing` → `in_transit` → `delivered` (смена статуса — техдолг) |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Агрегат, слепки, персистентность | Checkout BC (источник site-заказов) |
| `CreateOrderUseCase` — write с сайта | AggregatorIngress BC (источник aggregator-заказов) |
| Read API истории для SPA | Client BC (профиль; только `client_id` в строке) |
| | Операции кухни/курьера (не реализованы) |

## Хранение

Таблица `ORD_orders` — JSON-слепки блоков, `total_rubles`, `source`, без `updated_at`.

## Пути в коде

| Слой | Order |
|------|-------|
| Domain | `app/Domain/Order/` |
| Application | `app/Application/Order/` |
| Infrastructure | `app/Infrastructure/Order/` |
| HTTP | `app/Http/Controllers/Api/OrderController.php` |
| Filament | `app/Filament/Order/` — read-only |
| SPA | `resources/js/stores/orderStore.js`, `orderApi.js` |

## Аудит (состояние)

### Готово

- Domain → Infrastructure → `CreateOrderUseCase` + listener `OnCheckoutConfirmed`.
- `CreateOrderFromIngressUseCase` для агрегаторов.
- `POST /api/checkout/{id}/confirm` возвращает поле `order`.
- `GET /api/order`, `GET /api/order/{id}` — история / детали клиента.
- Filament: список + просмотр, колонка «Источник», партнёр.

### Техдолг

| # | Тема |
|---|------|
| 1 | Смена статуса заказа (use case + Filament action) |
| 2 | `POST /api/order` legacy на фронте — создание только через checkout |
| 3 | Feature-тесты read API и listener |
| 4 | SPA не использует `GET /api/order/{id}` |
