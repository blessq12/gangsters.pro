# MarketingContent — инфраструктура

Реализация доменных портов, Eloquent-модели и работа с медиа.

## Таблицы

| Таблица | Назначение |
|---------|------------|
| `MKT_banners` | Баннеры карусели |
| `MKT_promotions` | Акции витрины |

Миграция: `database/migrations/2026_06_14_160000_create_mkt_marketing_content_tables.php`.

### `MKT_banners`

| Колонка | Тип | Default |
|---------|-----|---------|
| `id` | bigint PK | auto |
| `title` | string | — |
| `description` | text nullable | — |
| `image_desktop` | string nullable | — |
| `image_mobile` | string nullable | — |
| `sort_order` | unsignedInteger | 0 |
| `is_active` | boolean | true |
| `created_at`, `updated_at` | timestamps | — |

### `MKT_promotions`

| Колонка | Тип | Default |
|---------|-----|---------|
| `id` | bigint PK | auto |
| `title` | string | — |
| `body` | longText nullable | — |
| `image` | string nullable | — |
| `sort_order` | unsignedInteger | 0 |
| `is_active` | boolean | true |
| `created_at`, `updated_at` | timestamps | — |

## Eloquent-модели

| Модель | Таблица | Особенности |
|--------|---------|-------------|
| `MKT_Banner` | `MKT_banners` | Lifecycle медиа, auto `sort_order` при create |
| `MKT_Promotion` | `MKT_promotions` | То же для поля `image` |

### Auto `sort_order` (обе модели, hook `creating`)

Если `sort_order === null` → `max(sort_order) + 1`, иначе `0`.

### Lifecycle медиа (`MKT_Banner`)

- `updating`: при смене `image_desktop` / `image_mobile` — `MarketingStoredMedia::deleteIfStored(oldPath)`
- `deleting`: удаление обоих image-полей с disk

`MKT_Promotion` — аналогично для `image`.

## Mappers

| Класс | Метод |
|-------|-------|
| `BannerMapper` | `toDomain(MKT_Banner): Banner` |
| `PromotionMapper` | `toDomain(MKT_Promotion): Promotion` |

Пустые строки после trim → `null` (`nullableString`).

## Repositories

| Класс | Порт | Запрос |
|-------|------|--------|
| `EloquentBannerRepository` | `BannerRepository` | `WHERE is_active = true ORDER BY sort_order, id` |
| `EloquentPromotionRepository` | `PromotionRepository` | то же |

## Ports (adapters)

| Класс | Порт |
|-------|------|
| `MarketingMediaUrlAdapter` | `MarketingMediaUrlPort` |

Делегирует в `PublicMediaUrl::resolve`.

### `PublicMediaUrl::resolve`

| Вход | Результат |
|------|-----------|
| `null` / пусто | `null` |
| `^https?://` | как есть |
| начинается с `/` | `asset($path)` |
| иначе | `Storage::disk('public')->url($path)` |

### `MarketingStoredMedia::deleteIfStored`

Удаляет с `public` disk только storage-relative пути.  
Skip: пусто, начинается с `/`, `^https?://` (seeder-пути `/images/*` не трогает).

## Композиция

`MarketingContentServiceProvider` (`config/app.php`):

```php
BannerRepository → EloquentBannerRepository
PromotionRepository → EloquentPromotionRepository
MarketingMediaUrlPort → MarketingMediaUrlAdapter
```

## Конфиг upload

`config/marketing.php`:

```php
'banner' => ['max_upload_kb' => env('MARKETING_BANNER_MAX_UPLOAD_KB', 0)],
'promotion' => ['max_upload_kb' => env('MARKETING_PROMOTION_MAX_UPLOAD_KB', 0)],
```

`0` = без лимита приложения (только PHP `upload_max_filesize`).

## Seeder

`Database\Seeders\MarketingContentSeeder` — 2 баннера + 2 акции; image paths `/images/banners/banner*.jpeg`. Вызывается из `DatabaseSeeder`.

## Тесты

Тестов BC **нет** (ни unit, ни feature).
