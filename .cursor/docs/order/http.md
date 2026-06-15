# Order — HTTP

## Write (сайт)

**Контроллер:** `App\Http\Controllers\Api\OrderDraftController`

| Действие | Метод | Use case | Request |
|----------|-------|----------|---------|
| `preview()` | `POST /api/order-drafts/preview` | `PreviewOrderDraftUseCase` | `PreviewOrderDraftRequest` |
| `store()` | `POST /api/orders` | `PlaceOrderUseCase` | `PlaceOrderRequest` |

### Preview body (фрагмент)

```json
{
  "cart": {
    "lines": [{ "product_id": 1, "quantity": 2 }],
    "selected_gift_product_id": 55
  },
  "delivery": { "method": "courier", "address": { ... } },
  "client": null,
  "payment": null
}
```

### Place body

Preview body **+** обязательные:

```json
{
  "client_request_id": "uuid-v4",
  "client": { "name": "...", "phone": "..." },
  "delivery": { "method": "pickup" },
  "payment": { "method": "cash" }
}
```

### Place response 201

```json
{
  "order": {
    "id": 42,
    "client_request_id": "uuid",
    "checkout_id": "uuid",
    "status": "new",
    ...
  }
}
```

## Read (история клиента)

**Контроллер:** `App\Http\Controllers\Api\OrderController`

| Действие | Метод | Auth |
|----------|-------|------|
| `index()` | `GET /api/order` | `auth.client` |
| `show()` | `GET /api/order/{orderId}` | `auth.client` |

### Preview response (фрагмент)

```json
{
  "cart": { "items": [...], "items_total_rubles": 1200, "payable_total_rubles": 1200, "promo_state": {} },
  "client": { "kind": "registered", "client_id": 1, "name": "...", "phone": "..." },
  "delivery": { "method": "courier", "address": { "street": "...", "latitude": 56.51, "longitude": 84.98 } },
  "payment": null,
  "benefits_progress": { "delivery": {}, "gift": {}, "complement": {} },
  "delivery_pricing": { "in_zone": true, "delivery_fee_rub": 0, "is_preview": false },
  "wizard": { "suggested_step": "payment", "can_confirm": false, "missing_blocks": ["payment"] },
  "order_preview": { "totals": {}, "complement_lines": [], "gift_cta": {} }
}
```

`delivery_pricing.in_zone`: `true` | `false` | `null` (нет координат / нет зоны в DLV).

### Координаты адреса в request

| Значение | Поведение |
|----------|-----------|
| поле отсутствует / `null` | geocode по street+house |
| `""` | как null (нормализация на клиенте и в `BuildOrderDraftFromInput`) |
| `(0, 0)` | geocode (не считается валидной точкой) |
| валидная пара | без geocode, сразу `PointInGeoJsonZone` |

## SPA

| Файл | Endpoint |
|------|----------|
| `resources/js/api/orderDraftApi.js` | preview, place |
| [spa.md](spa.md) | полное описание клиентского слоя |
| ~~`checkoutApi.js`~~ | **удалён** |

## Удалённые маршруты

~~`POST/PATCH /api/checkout/*`~~ — см. [Checkout (удалён)](../checkout/overview.md).
