# Order — потоки

## Создание заказа (сайт)

```mermaid
sequenceDiagram
    participant SPA
    participant CheckoutAPI
    participant CheckoutUC as ConfirmCheckoutUseCase
    participant Event as CheckoutConfirmed
    participant OrderH as OnCheckoutConfirmed
    participant OrderUC as CreateOrderUseCase
    participant DB as ORD_orders

    SPA->>CheckoutAPI: POST /api/checkout/{id}/confirm
    CheckoutUC->>Event: dispatch
    Event->>OrderH: handle
    OrderH->>OrderUC: CreateOrderDto
    OrderUC->>DB: insert (idempotent by checkout_id)
    OrderUC->>OrderUC: dispatch OrderCreated (новый)
    CheckoutAPI-->>SPA: checkout + order
```

## Создание заказа (агрегатор)

```mermaid
sequenceDiagram
    participant Agg as Агрегатор
    participant Ingress as IngressController
    participant UC as ReceiveExternalOrderUseCase
    participant OrderUC as CreateOrderFromIngressUseCase
    participant DB as ORD_orders

    Agg->>Ingress: POST /api/ingress/{partner}/orders
    Ingress->>UC: pipeline
    UC->>OrderUC: CreateOrderFromIngressDto
    OrderUC->>DB: insert (idempotent by partner+external_id)
    OrderUC->>OrderUC: dispatch OrderCreated (новый)
    Ingress-->>Agg: order_id
```

См. [AggregatorIngress flow](../aggregator-ingress/flow.md).

## Экспорт в системы учёта

После `OrderCreated` → [OrderAccountingExport flow](../order-accounting-export/flow.md) (Frontpad, iiko).

## История заказов (SPA)

```mermaid
sequenceDiagram
    participant SPA
    participant OrderAPI
    participant UC as ListClientOrdersUseCase
    participant DB as ORD_orders

    SPA->>OrderAPI: GET /api/order (Bearer)
    OrderAPI->>UC: clientId
    UC->>DB: where client_id order by created_at desc
    OrderAPI-->>SPA: { data: [...] }
```

Триггер загрузки: `useOrdersReadModel({ autoload: true })` в профиле клиента.

## Идемпотентность

| Канал | Ключ |
|-------|------|
| Сайт | `findByCheckoutId` в `CreateOrderUseCase` |
| Агрегатор | `findByPartnerAndExternalOrderId` в `CreateOrderFromIngressUseCase` |

## Гостевые заказы

`client_id` в `ORD_orders` = null → не попадают в `GET /api/order` (только registered).

Оператор видит гостевые заказы в Filament `/admin/orders`.
