# Order — Application

## Use cases

| Класс | Назначение |
|-------|------------|
| `CreateOrderUseCase` | Write: создать заказ из `CreateOrderDto`; идемпотентен по `checkout_id` |
| `ListClientOrdersUseCase` | Read: список заказов клиента → `OrderPresenter` |

## DTO

| DTO | Поля |
|-----|------|
| `CreateOrderDto` | checkoutId, cart, client, delivery, payment, createdAt |
| `ListClientOrdersDto` | clientId |

## Handler

`OnCheckoutConfirmed` — подписка на `CheckoutConfirmed`:

```
CheckoutConfirmed → CheckoutConfirmedOrderSnapshotMapper → CreateOrderDto → CreateOrderUseCase
```

## Presenter

`OrderPresenter::present(Order)`:

```json
{
  "id": 1,
  "checkout_id": "uuid",
  "status": "new",
  "total": 1500,
  "created_at": "ISO-8601",
  "client": { "kind", "client_id", "name", "phone", "email" },
  "delivery": { "method", "address", "comment", "scheduled_at" },
  "payment": { "method", "change_from_rubles" },
  "items": [{ "id", "quantity", "row_total", "product": { "name" } }]
}
```

`payment.method` для SPA: `card_courier` / `card_online` → `card`.

## ACL

`CheckoutConfirmedOrderSnapshotMapper` — маппинг Checkout VO → Order VO без утечки Checkout Entity в Order use case.
