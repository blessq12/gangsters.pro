# Storefront — потоки

## Загрузка приложения (SPA)

```mermaid
sequenceDiagram
    participant SPA
    participant Bootstrap as GET /storefront/bootstrap
    participant Cat as GetCatalogUseCase
    participant Del as GetDeliveryDataUseCase
    participant Prm as GetPromotionPolicyUseCase
    participant Co as Company use cases
    participant Mkt as GetMarketingContentUseCase

    SPA->>Bootstrap: app mount
    Bootstrap->>Cat: execute
    Bootstrap->>Del: execute
    Bootstrap->>Prm: execute
    Bootstrap->>Co: main + legals + documents
    Bootstrap->>Mkt: execute
    Bootstrap-->>SPA: unified JSON
    SPA->>SPA: Pinia stores hydrate
```

## Оформление заказа (после bootstrap)

Черновик живёт на клиенте; сервер участвует только в preview и place:

```
local draft (Pinia + sessionStorage)
  → POST /api/order-drafts/preview  (stateless)
  → POST /api/orders                (authoritative)
```

См. [Order flow — сайт](../order/flow.md).

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Bootstrap | `GET /api/storefront/bootstrap` | — |
| Legacy | `GET /api/catalog`, `/delivery`, … | — |
