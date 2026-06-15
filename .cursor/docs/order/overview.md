# Order — обзор BC

**Роль:** неизменяемый снимок заказа с сайта (**OrderDraft → PlaceOrder**) или ingress-заказа агрегатора. Хранится в `ORD_orders`.

## Источники заказа

| Источник | `OrderSource` | Ключ уникальности | Use case |
|----------|---------------|-------------------|----------|
| Сайт | `site` | `client_request_id` (колонка `checkout_id`) | `PlaceOrderUseCase` → `CreateOrderUseCase` |
| Агрегатор | `aggregator` | `partner_code` + `external_order_id` | `CreateOrderFromIngressUseCase` |

Подробнее про агрегаторов: [AggregatorIngress overview](../aggregator-ingress/overview.md).

## Семантика

| Термин | Смысл |
|--------|--------|
| **Order (агрегат)** | Immutable snapshot: cart, client, delivery, payment + status lifecycle |
| **client_request_id** | UUID с клиента для идемпотентности site-заказа (API); в БД — `checkout_id` |
| **OrderDraft** | In-memory черновик в Application (не персистится); preview + place |
| **partner_code / external_order_id** | ссылка на заказ партнёра (nullable для site) |
| **client_id** | Денормализация для списка заказов клиента (nullable для гостя / агрегатора) |
| **Статус** | `new` → … (смена статуса — техдолг) |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Агрегат Order, слепки, персистентность | Catalog / Delivery / Promotion — read через OrderDraft pipeline |
| `PlaceOrderUseCase`, `PreviewOrderDraftUseCase` | AggregatorIngress BC (aggregator write) |
| `CreateOrderUseCase` | Client BC (профиль; `client_id` в строке) |
| Read API истории для SPA | ~~Checkout BC~~ **удалён** |

## Хранение

Таблица `ORD_orders` — JSON-слепки блоков, `total_rubles`, `source`. Таблица `CHK_checkouts` **удалена**.

## Пути в коде

| Слой | Order |
|------|-------|
| Domain | `app/Domain/Order/` |
| OrderDraft (in-memory) | `app/Domain/Order/OrderDraft/` |
| Application | `app/Application/Order/`, `app/Application/Order/OrderDraft/` |
| Infrastructure | `app/Infrastructure/Order/` |
| HTTP | `OrderController`, `OrderDraftController` |
| Filament | `app/Filament/Order/` — read-only |
| SPA | `checkoutStore.js`, `orderDraftApi.js`, `storefrontStore.js` — см. [spa.md](spa.md) |

## Аудит (состояние 2026-06)

### Готово

- Site: `POST /api/orders` (PlaceOrder) + `POST /api/order-drafts/preview`.
- `ProcessOrderDraftPipeline`: benefits, geocode (с fallback при невалидных coords), validation → `CreateOrderUseCase`.
- Preview: инвалидация устаревших ответов на клиенте (`previewRequestSeq`).
- `OrderCreated` → OrderAccountingExport.
- Ingress: `CreateOrderFromIngressUseCase`.
- `GET /api/order`, `GET /api/order/{id}` — история клиента.
- Feature: `PlaceOrderTest`.

### Техдолг

| # | Тема |
|---|------|
| 1 | Смена статуса заказа (use case + Filament action) |
| 2 | Async export / outbox |
| 3 | SPA `GET /api/order/{id}` на success screen |

## См. также

- [flow.md](flow.md)
- [spa.md](spa.md) — корзина и визард на клиенте
- [Storefront bootstrap](../storefront/overview.md)
- [Checkout (удалён)](../checkout/overview.md)
