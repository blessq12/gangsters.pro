# AggregatorIngress — потоки

## Приём заказа (happy path)

```mermaid
sequenceDiagram
    participant Agg as Агрегатор
    participant API as IngressController
    participant UC as ReceiveExternalOrderUseCase
    participant Adp as IngressPartnerAdapter
    participant SKU as PartnerCatalogBinding
    participant OrderUC as CreateOrderFromIngressUseCase
    participant DB as ORD_orders

    Agg->>API: POST /api/ingress/{partner}/orders
    API->>UC: ReceiveExternalOrderDto
    UC->>UC: authenticate (X-Ingress-Api-Key)
    UC->>Adp: extractExternalOrderId
    alt уже принят
        UC-->>Agg: 200 order_id (idempotent)
    else новый заказ
        UC->>Adp: map(payload)
        UC->>SKU: resolve partner_sku → product_id
        UC->>OrderUC: CreateOrderFromIngressDto
        OrderUC->>DB: insert
        UC->>UC: audit (accepted)
        UC-->>Agg: 200 { order_id, status: accepted }
    end
```

## Идемпотентность

Повтор POST с тем же `(partner_code, external_order_id)`:

- **не** создаёт второй заказ;
- возвращает **200** с тем же `order_id`;
- пишет audit `result = idempotent`.

Ключ извлекается адаптером партнёра (`extractExternalOrderId`), не общим полем JSON.

## Отказ

| Причина | HTTP | Audit |
|---------|------|-------|
| Неверный API-key | 401 | `rejected` |
| Партнёр не настроен / disabled | 404 | — |
| Неизвестный SKU | 422 | `rejected` |
| Невалидный payload | 422 | `rejected` |

## Связь с Order BC

Checkout **не участвует**. Заказ создаётся напрямую:

```
IngressMappedOrder → CreateOrderFromIngressDto → Order::fromIngressSnapshot
```

Поля в `ORD_orders`: `source = aggregator`, `partner_code`, `external_order_id`, `checkout_id = null`.

## Где виден заказ

| Контур | Видимость |
|--------|-----------|
| Filament `/admin/orders` | да, колонка «Источник» + партнёр |
| `GET /api/order` (SPA) | нет (гостевой, без `client_id`) |

См. также [Order flow — создание с сайта](../order/flow.md).
