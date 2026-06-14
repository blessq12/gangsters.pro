# MarketingContent — слой приложения

## Сценарии

| Сценарий | Назначение |
|----------|------------|
| `GetMarketingContentUseCase` | Публичные баннеры и акции для SPA |

Файлы:
- `app/Application/MarketingContent/useCases/GetMarketingContentUseCase.php`
- `app/Application/MarketingContent/Presenter/MarketingContentPresenter.php`

## Поведение

1. Use case загружает активные баннеры и акции через `BannerRepository` / `PromotionRepository`.
2. `MarketingContentPresenter` собирает JSON-контракт через `MarketingMediaUrlPort` (публичные URL изображений).
3. `description` акции — plain-text excerpt из HTML body.

Админские мутации **не** проходят через Application — Filament пишет в `MKT_*` напрямую.
