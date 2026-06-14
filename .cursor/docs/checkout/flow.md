# Checkout — флоу

Сквозные сценарии BC.

## 1. Старт приложения (SPA)

```
useAppBootstrap()
  → bootstrapCheckoutSession()
  → sessionStorage draft? → restore snapshot
  → иначе POST /api/checkout
  → checkoutStore (Pinia)
```

Ключ session: `gangsters_checkout_session_v1`. Refresh в середине оформления **не** создаёт новый объект.

## 2. Добавление в корзину

```
CART_* domain event
  → useShoppingSessionProcess → cartStore
  → checkoutStore.updateCartLine()
  → PATCH /api/checkout/{id}/cart
  → UpdateCheckoutCartUseCase
  → CatalogPricingPort → CartLineSnapshot
  → save → JSON
```

## 3. Визард в доке корзины

```
CartDockPanel
  → CheckoutCartStep | Guest | Delivery | Payment | Confirm | Success
  → useCheckout (шаги + валидация UI)
```

| Шаг | Flush на бек |
|-----|--------------|
| Guest → Delivery | `PATCH .../client` |
| Delivery → Payment | `PATCH .../delivery` |
| Payment → Confirm | `PATCH .../payment` |
| Confirm | `POST .../confirm` |

Auth-пользователь: client block с `client_id` при входе в delivery-step.

## 4. Подтверждение

```
ConfirmCheckoutUseCase
  → Checkout::confirm()
  → save (status=confirmed)
  → Event::dispatch(CheckoutConfirmed)
  → OnCheckoutConfirmed (stub)
  → SPA: ORDER_CREATED → clear checkout session + empty cart UI
```

Order BC **не** создаёт заказ — только событие на беке и сброс на фронте.

## 5. Backend-only (без SPA)

```
POST /api/checkout → PATCH cart → PATCH client → PATCH delivery → PATCH payment → POST confirm
```

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | — (нет GET) | 6 command endpoint'ов |
| SPA | sessionStorage snapshot | checkoutApi PATCH/POST |
| Filament | — | — |

## Маппинг оплаты SPA ↔ API

| UI (`checkoutPaymentMethods.js`) | API `PaymentMethod` |
|----------------------------------|---------------------|
| `cash` | `cash` |
| `card` | `card_courier` |
