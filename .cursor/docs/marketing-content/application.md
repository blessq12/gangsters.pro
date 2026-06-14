# MarketingContent — слой приложения

## Сценарии

| Сценарий | Назначение |
|----------|------------|
| `GetMarketingContentUseCase` | Публичные баннеры и акции для SPA |

Файл: `app/Application/MarketingContent/useCases/GetMarketingContentUseCase.php`.

## Поведение

1. Загружает активные баннеры и акции через порты.
2. Собирает JSON-контракт: публичные URL изображений, `description` акции — plain-text excerpt из HTML body.

Админские мутации **не** проходят через Application — Filament пишет в `MKT_*` напрямую.
