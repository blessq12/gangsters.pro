# MarketingContent — HTTP-слой

**Контроллер:** `App\Http\Controllers\Api\MarketingContentController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `show()` | `GetMarketingContentUseCase` | `{ data: { banners, promotions } }` |
| `banners()` | то же | `{ data: [...] }` |
| `promotions()` | то же | `{ data: [...] }` |

Контроллер только делегирует use case. Read-only, без параметров.

## Контракт баннера

`id`, `title`, `description`, `image_desktop`, `image_mobile`, `image` (fallback)

## Контракт акции

`id`, `title`, `image`, `body` (HTML), `description` (plain excerpt для карточки)
