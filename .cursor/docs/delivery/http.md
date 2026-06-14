# Delivery — HTTP-слой

Тонкий адаптер между HTTP и сценариями приложения. Бизнес-логики в контроллере нет.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\DeliveryController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `show()` | `GetDeliveryDataUseCase::execute()` | JSON `{ data: { settings, zone } \| null }` |

Контроллер только делегирует use case и отдаёт `JsonResponse`. Валидация входа не требуется — read-only без параметров.

## Админка

HTTP для оператора (форма доставки, редактор карты) не проходит через `app/Http/Controllers/Api`. Исключение — вспомогательный маршрут панели:

- `DeliveryZoneMapEditorController` — iframe-редактор полигона (`/admin/delivery-zone-map-editor`), регистрируется в `AdminPanelProvider::routes()`.

Основное сохранение настроек — Filament/Livewire на `/admin/delivery`.

## Принцип

- API доставки = **один read-endpoint**.
- Авторизация витрины на доставку не навешена (публичные настройки).
- Админские запросы защищены middleware панели Filament (session + auth).
