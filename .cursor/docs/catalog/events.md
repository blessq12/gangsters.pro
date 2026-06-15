# Catalog — события

## Текущее состояние

В runtime BC **нет** доменных или интеграционных событий каталога:

- нет Laravel `Event` / `Listener` для категорий, товаров, наборов, тегов;
- нет outbox и публикации в другие контексты при сохранении из Filament;
- `GetCatalogUseCase` — синхронный read без побочных эффектов.

Изменения в админке видны витрине при следующем `GET /api/catalog` (общая БД).  
SPA может показывать устаревшие данные из localStorage до `catalogStore.fetchAll()`.

## Побочные эффекты без событий

| Действие | Эффект |
|----------|--------|
| Filament save/delete/reorder `PRD_*` | Следующий API read отдаёт новые данные |
| Upload image в relation manager | Файл на `public` disk; **не** в публичном API до доработки use case |
| Meta toggle в Filament | Влияет на Order ACL (gift/roll/complement) и bootstrap `promotion_meta` |

## Сейчас

Для агента: **не искать** `Catalog*Event` в коде — их нет.
