# MarketingContent — инфраструктура

## Таблицы

| Таблица | Назначение |
|---------|------------|
| `MKT_banners` | Баннеры карусели |
| `MKT_promotions` | Акции |

Миграция: `database/migrations/2026_06_14_160000_create_mkt_marketing_content_tables.php`.

## Eloquent-модели

- `MKT_Banner` — auto `sort_order` при create
- `MKT_Promotion` — auto `sort_order` при create

## Mappers

- `BannerMapper::toDomain`
- `PromotionMapper::toDomain`

## Repositories

- `EloquentBannerRepository`
- `EloquentPromotionRepository`

## Support

- `PublicMediaUrl::resolve` — путь storage / абсолютный URL → публичный URL для API

## Seeder

`database/seeders/MarketingContentSeeder.php` — демо-баннеры и акции со статичными `/images/banners/*`.
