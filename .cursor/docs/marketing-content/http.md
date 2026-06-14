# MarketingContent — HTTP-слой

Тонкий адаптер между HTTP и `GetMarketingContentUseCase`. Бизнес-логики в контроллере нет.

**Контроллер:** `App\Http\Controllers\Api\MarketingContentController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `show()` | `execute()` | `{ data: { banners, promotions } }` |
| `banners()` | `execute()` → `$data['banners']` | `{ data: [...] }` |
| `promotions()` | `execute()` → `$data['promotions']` | `{ data: [...] }` |

Контроллер только делегирует use case. Read-only, без параметров, без дополнительной авторизации (группа `api`).

`banners()` и `promotions()` каждый раз вызывают полный `execute()` (оба репозитория), хотя отдают срез.

## Контракт баннера

`id`, `title`, `description`, `image_desktop`, `image_mobile`, `image` (fallback: desktop ?? mobile)

## Контракт акции

`id`, `title`, `image`, `body` (HTML), `description` (plain-text excerpt, до 240 символов)

## SPA

Frontend использует:
- `GET /api/marketing/banners`
- `GET /api/marketing/promotions`

`GET /api/marketing` (combined) в SPA **не** вызывается.

## Принцип

- API MarketingContent = **три read-endpoint'а**, write-endpoint'ов нет.
- Админские мутации — только Filament (`/admin/marketing`).
