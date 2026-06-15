# Order — потоки

## Оформление и создание заказа (сайт)

```mermaid
sequenceDiagram
    participant SPA
    participant Bootstrap as GET /storefront/bootstrap
    participant Preview as POST /order-drafts/preview
    participant Place as POST /orders
    participant Pipeline as ProcessOrderDraftPipeline
    participant Promo as EvaluatePromotionBenefits
    participant OrderUC as CreateOrderUseCase
    participant DB as ORD_orders

    SPA->>Bootstrap: app open
    Bootstrap-->>SPA: catalog + promotion + delivery + ...

    Note over SPA: wizard steps — local Pinia draft only

    SPA->>Preview: debounced draft JSON
    Preview->>Pipeline: build + sync benefits
    Pipeline->>Promo: evaluate
    Preview-->>SPA: cart + benefits + order_preview

    SPA->>Place: draft + client_request_id
    Place->>Pipeline: forPlace=true
    Place->>OrderUC: CreateOrderDto
    OrderUC->>DB: insert (idempotent)
    OrderUC->>OrderUC: dispatch OrderCreated
    Place-->>SPA: { order }
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

После `OrderCreated` → [OrderAccountingExport flow](../order-accounting-export/flow.md).

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

## Идемпотентность

| Канал | Ключ |
|-------|------|
| Сайт | `findByClientRequestId` / `checkout_id` в `CreateOrderUseCase` |
| Агрегатор | `findByPartnerAndExternalOrderId` |

## Гостевые заказы

`client_id` в `ORD_orders` = null → не попадают в `GET /api/order`. Оператор видит их в Filament `/admin/orders`.
