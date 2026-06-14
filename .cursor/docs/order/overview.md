# Order — обзор BC

**Роль:** неизменяемый снимок **подтверждённого** оформления (Checkout). Создаётся один раз при `CheckoutConfirmed`, хранится в `ORD_orders`.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Order (агрегат)** | Immutable snapshot: cart, client, delivery, payment + статус lifecycle |
| **checkout_id** | UUID ссылки на `CHK_checkouts` (unique) |
| **client_id** | Денормализация для списка заказов клиента (nullable для гостя) |
| **Статус** | `new` → `preparing` → `in_transit` → `delivered` (смена статуса — техдолг) |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Агрегат, слепки, персистентность | Checkout BC (источник события) |
| `CreateOrderUseCase` — единственный write | Client BC (профиль; только `client_id` в строке) |
| Read API истории для SPA | Операции кухни/курьера (не реализованы) |

## Хранение

Таблица `ORD_orders` — JSON-слепки блоков, `total_rubles`, без `updated_at`.

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
- `POST /api/checkout/{id}/confirm` возвращает поле `order`.
- `GET /api/order` — история заказов авторизованного клиента.
- Filament: список + просмотр по табам.

### Техдолг

| # | Тема |
|---|------|
| 1 | Смена статуса заказа (use case + Filament action) |
| 2 | `POST /api/order` legacy на фронте — создание только через checkout |
| 3 | Feature-тесты API и listener |
| 4 | `GET /api/order/{id}` для детальной карточки |
