# Delivery — HTTP

## Публичный read

**Контроллер:** `App\Http\Controllers\Api\DeliveryController`

| Действие | Сценарий | Ответ |
|----------|----------|--------|
| `show()` | `GetDeliveryDataUseCase::execute()` | JSON `{ settings, zone }` |

Read-only, без auth.

## Bootstrap

`GET /api/storefront/bootstrap` → блок `delivery` (тот же use case).

## OrderDraft (косвенно)

Delivery BC **не имеет** write HTTP. Геокод и pricing — внутри:

| Method | URI | Связь с Delivery |
|--------|-----|------------------|
| POST | `/api/order-drafts/preview` | `PrepareOrderDraftDeliveryAddress` |
| POST | `/api/orders` | то же + persist snapshot в Order |

В ответе preview: `delivery`, `delivery_pricing` (`in_zone`, surcharges).

## Удалено

~~`PATCH /api/checkout/{checkoutId}/delivery`~~
