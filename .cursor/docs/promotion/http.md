# Promotion — HTTP

## Основной контракт: Storefront bootstrap

`GET /api/storefront/bootstrap` → блок `promotion`:

```json
{
  "gift": {
    "active": true,
    "pickup_min_kopecks": 100000,
    "courier_min_kopecks": 180000
  },
  "complement": {
    "active": true,
    "rolls_per_set": 3
  },
  "delivery_benefit": {
    "active": true,
    "free_threshold_kopecks": 100000,
    "outside_zone_surcharge_kopecks": 20000
  }
}
```

Если конфигурации нет — `promotion: null`.

## Preview / place (расчёт benefits)

`POST /api/order-drafts/preview` и `POST /api/orders` возвращают в теле preview:

- `benefits_progress`
- `delivery_pricing`
- `promo_state` (gift eligibility, candidates)

Write-endpoint'ов у Promotion **нет**.

## Опциональный legacy

`GET /api/promotion` — отдельный read, если нужен без bootstrap. На витрине не используется.
