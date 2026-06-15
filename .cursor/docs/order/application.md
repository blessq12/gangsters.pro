# Order — Application

## Use cases

| Класс | Назначение |
|-------|------------|
| `CreateOrderUseCase` | Write (сайт): из `CreateOrderDto`; идемпотентен по `checkout_id` |
| `CreateOrderFromIngressUseCase` | Write (агрегатор): из `CreateOrderFromIngressDto`; идемпотентен по `(partner, external_order_id)` |
| `ListClientOrdersUseCase` | Read: список заказов клиента → `OrderPresenter` |
| `GetOrderUseCase` | Read: детали заказа клиента с ACL |

## DTO

| DTO | Поля |
|-----|------|
| `CreateOrderDto` | checkoutId, cart, client, delivery, payment, createdAt |
| `CreateOrderFromIngressDto` | partnerCode, externalOrderId, cart, client, delivery, payment, createdAt |
| `ListClientOrdersDto` | clientId |
| `GetOrderDto` | orderId, clientId |

## Handler

`OnCheckoutConfirmed` — подписка на `CheckoutConfirmed`:

```
CheckoutConfirmed → CheckoutConfirmedOrderSnapshotMapper → CreateOrderDto → CreateOrderUseCase
```

Ingress-заказы **не** через handler — см. [AggregatorIngress application](../aggregator-ingress/application.md).

## Presenter

`OrderPresenter::present(Order)`:

```json
{
  "id": 1,
  "source": "site",
  "checkout_id": "uuid",
  "partner_code": null,
  "external_order_id": null,
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

| Mapper | Направление |
|--------|-------------|
| `CheckoutConfirmedOrderSnapshotMapper` | Checkout VO → Order VO |
| `IngressMappedOrderToCreateOrderMapper` | AggregatorIngress → `CreateOrderFromIngressDto` |
