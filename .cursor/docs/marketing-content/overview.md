# MarketingContent — обзор BC

**Роль:** публичный маркетинговый контент витрины — баннеры главной (карусель) и акции (промо-карточки).

> В коде BC называется **MarketingContent** (`MKT_*`). Не путать с BC **Promotion** (`PRM_*`) — правила подарков и тарифов checkout; это другой контекст.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Баннер** | Слайд карусели: заголовок, описание, изображения desktop/mobile, порядок, активность |
| **Акция** | Промо-карточка витрины: заголовок, изображение, тело (HTML), порядок, активность |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| CRUD баннеров и акций, порядок (`sort_order`), активность (`is_active`) | Каталог, корзина, checkout |
| Публичный read API `GET /api/marketing/*` | BC Promotion — правила подарков/доставки в корзине |
| Резолв публичных URL изображений (`MarketingMediaUrlPort`) | MarketingContent **не** влияет на расчёт benefits |

Публичный контракт — **только чтение** (`GET /api/marketing/*`).  
Запись — **только Filament** (`/admin/marketing`).  
SPA читает API через `marketingStore` → `marketingService` (отдельные запросы `/banners` и `/promotions`).

## Хранение

Таблицы `MKT_*`: `MKT_banners`, `MKT_promotions`.

Миграция: `database/migrations/2026_06_14_160000_create_mkt_marketing_content_tables.php`.  
Сид: `Database\Seeders\MarketingContentSeeder` (2 баннера + 2 акции, image paths `/images/banners/*`).

## Пути в коде

| Слой | MarketingContent |
|------|------------------|
| Domain | `app/Domain/MarketingContent/` |
| Application | `app/Application/MarketingContent/` — use case + presenter |
| Infrastructure | `app/Infrastructure/MarketingContent/` |
| HTTP | `app/Http/Controllers/Api/MarketingContentController.php` |
| Filament | `app/Filament/MarketingContent/` |
| Composition | `app/Providers/MarketingContentServiceProvider.php` |
| Config | `config/marketing.php` |
| SPA | `resources/js/stores/marketingStore.js`, `resources/js/features/marketing/`, `resources/js/domain/marketing/` |

## Аудит (состояние на 2026-06-14)

### Готово

- Полная вертикаль read: Domain → Application → Infrastructure → HTTP (3 endpoint'а).
- Filament hub `/admin/marketing` с табами «Баннеры» / «Акции», reorder, CRUD через скрытые ресурсы.
- Upload изображений: `public` disk, `marketing/banners/{desktop,mobile}`, `marketing/promotions`.
- Lifecycle медиа: удаление старых файлов при update/delete (`MarketingStoredMedia`).
- Защита seeder-путей `/images/*` при edit без новой загрузки (`PreservesMarketingMediaOnEmptyUpload`).
- SPA: `marketingStore`, `useMarketingReadModel`, карусель (`HomeJumbotron*`), блок акций (`HomePromotions*`), prefetch в `MainLayout*`.

### Пробелы / техдолг

- Write в админке **мимо** Application (Filament → Eloquent напрямую).
- Нет доменных событий.
- **Нет тестов** BC (API, use case, mapper, Filament).
- `GET /api/marketing` (combined) есть в API, но SPA использует только `/banners` и `/promotions`.
- Возможен двойной fetch на главной: `MainLayout*` prefetch + `useMarketingReadModel` autoload.
- `sort_order` в формах нет — только reorder в hub table и auto-increment при create.
