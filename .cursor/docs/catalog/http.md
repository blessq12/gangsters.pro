# Catalog — HTTP-слой

Тонкий адаптер между HTTP и `GetCatalogUseCase`. Бизнес-логики в контроллере нет.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\CatalogController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `show()` | `GetCatalogUseCase::execute()` | JSON `{ categories: [...] }` |

Контроллер делегирует use case и отдаёт результат **напрямую** (без обёртки `{ data: ... }`).

Read-only, без параметров, без auth middleware сверх группы `api`.

**Bootstrap:** тот же `GetCatalogUseCase`, но через `GET /api/storefront/bootstrap` с `promotion_meta` на product items.

## Админка

HTTP для оператора не проходит через `CatalogController`. Filament/Livewire обслуживает `/admin/catalog`.

## Принцип

- API каталога = **один read-endpoint**.
- Авторизация витрины на каталог не навешена (публичное меню).
- Админские запросы защищены middleware панели Filament (session + auth).
