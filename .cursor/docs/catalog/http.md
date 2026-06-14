# Catalog — HTTP-слой

Тонкий адаптер между HTTP и сценариями приложения. Бизнес-логики в контроллере нет.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\CatalogController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `show()` | `GetCatalogUseCase::execute()` | JSON `{ categories: [...] }` |

Контроллер только делегирует use case и отдаёт `JsonResponse`. Валидация входа не требуется — read-only без параметров.

## Админка

HTTP для оператора не проходит через `app/Http/Controllers` каталога. Filament/Livewire обслуживает `/admin/catalog` как отдельный UI-контур с собственной маршрутизацией панели.

## Принцип

- API каталога = **один read-endpoint**.
- Авторизация витрины на каталог не навешена (публичное меню).
- Админские запросы защищены middleware панели Filament (session + auth).
