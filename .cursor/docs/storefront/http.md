# Storefront — HTTP

**Контроллер:** `App\Http\Controllers\Api\StorefrontController`

## `GET /api/storefront/bootstrap`

Read-only, без auth. Делегирует `GetStorefrontBootstrapUseCase`.

### Ответ 200

```json
{
  "version": "2026-06-16T12:00:00+00:00",
  "catalog": { "categories": [ ... ] },
  "delivery": { "settings": { ... }, "zone": { ... } } | null,
  "promotion": {
    "gift": { "active": true, "pickup_min_kopecks": 100000, "courier_min_kopecks": 180000 },
    "complement": { "active": true, "rolls_per_set": 3 },
    "delivery_benefit": {
      "active": true,
      "free_threshold_kopecks": 100000,
      "outside_zone_surcharge_kopecks": 20000
    }
  } | null,
  "company": {
    "main": { ... } | null,
    "legals": { ... } | null,
    "documents": [ ... ]
  },
  "marketing": {
    "banners": [ ... ],
    "promotions": [ ... ]
  }
}
```

### Поля catalog item (product)

Дополнительно к прежнему контракту каталога:

```json
"promotion_meta": {
  "counts_as_roll": false,
  "gift_candidate": true,
  "complement_set": false
}
```

## Принцип

- Один round-trip вместо 4–5 запросов при открытии приложения.
- Write-endpoint'ов нет.
