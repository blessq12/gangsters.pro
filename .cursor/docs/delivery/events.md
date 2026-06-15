# Delivery — события

## Текущее состояние

В runtime BC **нет** доменных или интеграционных событий доставки:

- нет Laravel `Event` / `Listener` для изменения тарифов или зоны;
- нет outbox и публикации в другие контексты при сохранении из Filament;
- `GetDeliveryDataUseCase` — синхронный read без побочных эффектов;
- геокодирование в OrderDraft pipeline — синхронный вызов порта без событий.

Изменения в админке сразу видны при следующем `GET /api/delivery` (общая БД, кэша на read нет).

## Побочные эффекты без событий

| Действие | Эффект |
|----------|--------|
| Filament save `DLV_configuration` | Следующий `findPublic()` / `GET /api/delivery` отдаёт новые данные |
| `POST order-drafts/preview` / `POST orders` | Геокод (если courier) + `delivery_pricing` в ответе |

## Сейчас

Для агента: **не искать** `Delivery*Event` в коде — их нет.

При добавлении write use cases или инвалидации кэша — обновить этот файл.
