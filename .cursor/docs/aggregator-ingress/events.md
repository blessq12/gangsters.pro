# AggregatorIngress — события

BC **не публикует** доменных событий при приёме заказа (техдолг: `IngressOrderReceived` для аналитики).

## Исходящие (план)

| Событие | Когда |
|---------|--------|
| `IngressOrderReceived` | после успешного `CreateOrderFromIngressUseCase` |
| `OrderCreated` | из Order BC — см. [OrderAccountingExport events](../order-accounting-export/events.md) |

## Входящие

Нет — ingress синхронный HTTP webhook, не event-driven.

## Audit (не доменное событие)

`IngressAuditRepository::record` — персистентный лог в `ING_ingress_audits`:

| result | Смысл |
|--------|--------|
| `accepted` | создан новый заказ |
| `idempotent` | повтор того же external_order_id |
| `rejected` | ошибка до/во время создания |

Сырой `raw_payload` хранится для разбора инцидентов с партнёром.

## Смежные BC

- Order BC слушает `CheckoutConfirmed` (сайт), но **не** ingress — см. [Order events](../order/events.md).
