# MarketingContent — потоки

## Витрина (read)

1. SPA (позже) → `GET /api/marketing/banners` / `promotions`.
2. `MarketingContentController` → `GetMarketingContentUseCase`.
3. Repositories → активные записи `MKT_*` по `sort_order`.
4. Use case маппит URL изображений и excerpt акции.

## Админка (write)

1. Оператор → `/admin/marketing`.
2. Hub table → create/edit форма Filament.
3. Сохранение в `MKT_banners` / `MKT_promotions` (Eloquent).
4. Следующий API read отдаёт обновлённые данные.
