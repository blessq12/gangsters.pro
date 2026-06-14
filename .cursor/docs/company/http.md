# Company — HTTP-слой

Тонкий адаптер между HTTP и сценариями приложения.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\CompanyController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `main()` | `GetCompanyDataUseCase::execute()` | JSON `{ data: {...} \| null }` |
| `legals()` | `GetCompanyLegalDataUseCase::execute()` | JSON `{ data: {...} \| null }` |
| `documents()` | `GetCompanyDocumentsUseCase::execute()` | JSON `{ data: [...] }` |

Валидация входа не требуется — read-only без параметров.

## Админка

Мутации — Filament `/admin/company`, не через этот контроллер.

## Принцип

- Три read-endpoint'а под префиксом `/api/company`.
- Публичное чтение без авторизации.
- Админка — middleware панели Filament.
