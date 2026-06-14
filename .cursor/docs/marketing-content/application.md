# MarketingContent — слой приложения

Оркестрация публичного read-сценария: загрузка активных записей через порты, сборка JSON-контракта.

## Сценарии

| Сценарий | Назначение |
|----------|------------|
| `GetMarketingContentUseCase` | Публичные баннеры и акции для SPA |

Файлы:
- `app/Application/MarketingContent/useCases/GetMarketingContentUseCase.php`
- `app/Application/MarketingContent/Presenter/MarketingContentPresenter.php`

## Поведение `GetMarketingContentUseCase`

1. `BannerRepository::findActiveOrdered()`
2. `PromotionRepository::findActiveOrdered()`
3. `MarketingContentPresenter::present($banners, $promotions)`

Зависимости — только Domain ports + presenter.

**Возврат use case** (без HTTP-обёртки):

```json
{
  "banners": [...],
  "promotions": [...]
}
```

## `MarketingContentPresenter`

Зависимость: `MarketingMediaUrlPort`.

### Баннер (`presentBanner`)

| Поле | Источник |
|------|----------|
| `id` | `$banner->id()` |
| `title` | `$banner->title()` |
| `description` | `$banner->description()` |
| `image_desktop` | `resolve(imageDesktop)` |
| `image_mobile` | `resolve(imageMobile)` |
| `image` | `image_desktop ?? image_mobile` |

### Акция (`presentPromotion`)

| Поле | Источник |
|------|----------|
| `id` | `$promotion->id()` |
| `title` | `$promotion->title()` |
| `image` | `resolve(image)` |
| `body` | `$promotion->body()` (HTML как в БД) |
| `description` | plain-text excerpt из `body` |

### Логика `plainTextExcerpt`

1. `null` → `null`
2. `strip_tags` + `html_entity_decode(ENT_QUOTES | ENT_HTML5, UTF-8)` + `trim`
3. Пустая строка → `null`
4. `mb_strlen <= 240` → весь текст
5. Иначе → `mb_substr(..., 0, 237) + '…'`

## Админские мутации

**Не** проходят через Application. Filament пишет в `MKT_*` напрямую (см. `filament.md`).

## DTO / Command-сценарии

В BC **нет** DTO и write use cases — один read-сценарий + presenter.
