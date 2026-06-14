# MarketingContent — события

## Текущее состояние

В runtime BC **нет** доменных или интеграционных событий:

- нет Laravel `Event` / `Listener` при изменении баннеров или акций;
- нет outbox и публикации в другие контексты при сохранении из Filament;
- `GetMarketingContentUseCase` — синхронный read без побочных эффектов.

Изменения в админке сразу видны при следующем `GET /api/marketing/*` (общая БД, кэша на read нет).

## Побочные эффекты без событий

| Действие | Эффект |
|----------|--------|
| Filament save/delete/reorder `MKT_*` | Следующий API read отдаёт новые данные |
| Update image path | `MarketingStoredMedia` удаляет старый файл с `public` disk (кроме `/images/*`, http) |
| Delete banner/promotion | Удаление связанных media files с disk |

## Сейчас

Для агента: **не искать** `Marketing*Event` в коде — их нет.

При добавлении write use cases или CDN-инвалидации — обновить этот файл.
