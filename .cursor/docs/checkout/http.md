# Checkout — HTTP-слой

Тонкий адаптер: `FormRequest` → DTO → use case → JSON.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\CheckoutController`

| Действие | HTTP | Use case | Тело запроса |
|----------|------|----------|--------------|
| `store()` | `POST /api/checkout` | `CreateCheckoutUseCase` | — |
| `updateCart()` | `PATCH /api/checkout/{checkoutId}/cart` | `UpdateCheckoutCartUseCase` | `UpdateCheckoutCartRequest` |
| `setClient()` | `PATCH .../client` | `SetCheckoutClientUseCase` | `SetCheckoutClientRequest` |
| `setDelivery()` | `PATCH .../delivery` | `SetCheckoutDeliveryUseCase` | `SetCheckoutDeliveryRequest` |
| `setPayment()` | `PATCH .../payment` | `SetCheckoutPaymentUseCase` | `SetCheckoutPaymentRequest` |
| `confirm()` | `POST .../confirm` | `ConfirmCheckoutUseCase` | — |

`store()` возвращает **201**; остальные — **200**.

Ответ — результат `CheckoutPresenter` (см. `application.md`).

## FormRequest

| Класс | Ключевые правила |
|-------|------------------|
| `UpdateCheckoutCartRequest` | `product_id`, `quantity` (min 0), `payload?` |
| `SetCheckoutClientRequest` | `client_id?`, `name?`, `phone?`, `email?` |
| `SetCheckoutDeliveryRequest` | `method` enum, `address` required_if courier |
| `SetCheckoutPaymentRequest` | `method` in `PaymentMethod::placementValues()`, `change_from_rubles?` |

## Legacy (не подключено к роутам)

| Класс | Примечание |
|-------|------------|
| `PatchCheckoutDraftRequest` | Старый shopping draft; enum из Checkout |
| `UpsertCartLineRequest`, `MigrateLocalShoppingRequest` | Shopping BC, роутов нет |

## Принцип

- Контроллер не содержит бизнес-логики; сборка `DeliveryAddress` и enum — граница HTTP.
- Авторизация **не** навешена (публичный API).
- Domain exceptions пока не преобразуются в structured JSON errors (глобальный handler).

## SPA-клиент

`resources/js/api/checkoutApi.js` — зеркало endpoint'ов.
