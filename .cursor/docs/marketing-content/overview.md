# MarketingContent — обзор BC

**Роль:** публичный маркетинговый контент витрины — баннеры главной и акции.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Баннер** | Слайд карусели: заголовок, описание, изображения desktop/mobile |
| **Акция** | Промо-карточка: заголовок, изображение, тело (HTML) |

## Границы

- **Внутри BC:** CRUD баннеров и акций, порядок, активность, публичный read API.
- **Снаружи:** каталог, корзина, checkout-promotions (подарки) — другие контексты.
- Публичный контракт — **только чтение** (`GET /api/marketing/*`).
- Запись — **только Filament** (`/admin/marketing`).
- SPA пока читает `/api/system/banners|promotions` — артефакт; целевые endpoint'ы — `/api/marketing/*`.

## Хранение

Таблицы `MKT_*`: `MKT_banners`, `MKT_promotions`.

## Пути в коде

| Слой | MarketingContent |
|------|------------------|
| Domain | `app/Domain/MarketingContent/` |
| Application | `app/Application/MarketingContent/` |
| Infrastructure | `app/Infrastructure/MarketingContent/` |
| HTTP | `app/Http/Controllers/Api/MarketingContentController.php` |
| Filament | `app/Filament/MarketingContent/` |
