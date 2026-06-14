# MarketingContent — слой домена

## Сущности

| Элемент | Смысл |
|---------|--------|
| `Banner` | Баннер: title, description, imageDesktop, imageMobile, sortOrder, isActive |
| `Promotion` | Акция: title, body (HTML), image, sortOrder, isActive |

## Порты

| Порт | Ответственность |
|------|-----------------|
| `BannerRepository` | Активные баннеры по sort_order (`findActiveOrdered`) |
| `PromotionRepository` | Активные акции по sort_order (`findActiveOrdered`) |
| `MarketingMediaUrlPort` | Путь/URL изображения → публичный URL для API |

Домен **не знает** про `MKT_*`, Eloquent и `Storage`.
