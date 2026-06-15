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

## SPA

| Файл | Endpoint |
|------|----------|
| `resources/js/api/orderDraftApi.js` | preview, place |
| ~~`checkoutApi.js`~~ | **удалён** |

## Удалённые маршруты

~~`POST/PATCH /api/checkout/*`~~ — см. [Checkout (удалён)](../checkout/overview.md).
