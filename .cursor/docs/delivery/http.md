# Delivery — HTTP-слой

Тонкий адаптер между HTTP и сценариями приложения Delivery BC. Бизнес-логики в контроллере нет.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\DeliveryController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `show()` | `GetDeliveryDataUseCase::execute()` | JSON `{ data: { settings, zone } \| null }` |

Контроллер только делегирует use case и отдаёт `JsonResponse`. Валидация входа не требуется — read-only без параметров.

Авторизация не навешена (публичные настройки).

## Админка

HTTP для оператора (форма доставки, редактор карты) не проходит через `app/Http/Controllers/Api`. Исключение — вспомогательный маршрут панели:

| Контроллер | Маршрут | Смысл |
|------------|---------|--------|
| `DeliveryZoneMapEditorController` | `/admin/delivery-zone-map-editor` | iframe-редактор полигона, передаёт Yandex API keys |

Регистрируется в `AdminPanelProvider::routes()`.

Основное сохранение настроек — Filament/Livewire на `/admin/delivery`.

## Связанный HTTP (другие BC)

Конфиг Delivery **косвенно** участвует в checkout API:

| Метод | Путь | Связь с Delivery |
|-------|------|------------------|
| `PATCH` | `/api/checkout/{checkoutId}/delivery` | `SetCheckoutDeliveryUseCase` → `PrepareCheckoutDeliveryAddress` (геокод + конфиг) → pricing через Promotion |

Request: `App\Http\Requests\Checkout\SetCheckoutDeliveryRequest`.

В ответе checkout: `delivery`, `delivery_pricing` (`in_zone`, `outside_zone_surcharge_*`), `benefits_progress.delivery`.

## Принцип

- API Delivery BC = **один read-endpoint** (`GET /api/delivery`).
- Нет публичных write-endpoint'ов доставки.
- Админские запросы защищены middleware панели Filament (session + auth).
