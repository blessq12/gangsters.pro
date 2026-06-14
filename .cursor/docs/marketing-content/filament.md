# MarketingContent — Filament

Хаб `/admin/marketing` с табами **Баннеры** и **Акции**.

## Ресурсы

| Ресурс | Slug | Навигация |
|--------|------|-----------|
| `MarketingContentResource` | `marketing` | Да (хаб) |
| `BannerResource` | `marketing/banners` | Скрыт |
| `PromotionResource` | `marketing/promotions` | Скрыт |

## Hub tables

- `BannersHubTable` — reorder `sort_order`, create/edit/delete
- `PromotionsHubTable` — то же

## Формы

- Баннер: title, description, image_desktop, image_mobile, is_active
- Акция: title, image, body (HTML textarea), is_active

Upload: `public` disk, `marketing/banners/*`, `marketing/promotions/*`. Лимиты — `config/marketing.php`.

После create/edit — редирект на хаб с нужным `?tab=`.
