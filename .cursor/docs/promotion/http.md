# Promotion — HTTP

Публичный API **не реализован**. Опциональный контракт:

## `GET /api/promotion`

Ответ 200:

```json
{
  "data": {
    "gift": {
      "active": true,
      "pickup_min_order_kopecks": 100000,
      "courier_min_order_kopecks": 180000
    },
    "delivery": {
      "active": true,
      "free_threshold_kopecks": 100000,
      "outside_zone_surcharge_kopecks": 20000
    }
  }
}
```

Если строки id=1 нет — `data: null` (как Delivery).

Write-endpoint'ов нет. Альтернатива: не вводить маршрут, а отдавать те же поля в теле ответов checkout после evaluator.
