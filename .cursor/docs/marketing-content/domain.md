# MarketingContent — слой домена

## Сущности

| Элемент | Смысл |
|---------|--------|
| `Banner` | Баннер: title, description, imageDesktop, imageMobile, sortOrder, isActive |
| `Promotion` | Акция: title, body (HTML), image, sortOrder, isActive |

## Репозитории (порты)

| Порт | Ответственность |
|------|-----------------|
| `BannerRepository` | Активные баннеры по sort_order (`findActiveOrdered`) |
| `PromotionRepository` | Активные акции по sort_order (`findActiveOrdered`) |

Домен **не знает** про `MKT_*` и Eloquent.
