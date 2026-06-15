# AggregatorIngress — слой домена

Ядро BC без Laravel/HTTP.

## Value Objects

| VO | Смысл |
|----|--------|
| `IngressMappedOrder` | Нормализованный заказ после ACL (клиент, доставка, оплата, строки с `partner_sku`) |
| `IngressMappedLine` | `partnerSku`, `quantity`, `unitPriceRubles` |
| `IngressMappedAddress` | street, house, entrance?, apartment? |
| `ResolvedPartnerProduct` | `productId`, `productName` — результат резолва SKU |

## Repository (ports)

| Порт | Методы |
|------|--------|
| `PartnerCatalogBindingRepository` | `resolve(partnerCode, partnerSku): ?ResolvedPartnerProduct` |
| `IngressAuditRepository` | `record(partnerCode, externalOrderId, result, rawPayload, orderId?)` |

## Exception

| Класс | Когда | HTTP (через Handler) |
|-------|-------|------------------------|
| `IngressAuthenticationFailedException` | неверный API-key | 401 |
| `PartnerNotConfiguredException` | нет в config / disabled | 404 |
| `UnknownPartnerSkuException` | нет binding | 422 |
| `IngressInvariantViolation` | битый payload адаптера | 422 |

## Зависимости

- Domain **не** импортирует Application / Infrastructure.
- Order Domain расширен отдельно: `OrderSource`, `OrderAggregatorReference` — см. [Order domain](../order/domain.md).

## Расширение Order (смежный BC)

| Элемент | Смысл |
|---------|--------|
| `OrderSource::Aggregator` | источник заказа |
| `OrderAggregatorReference` | `partnerCode` + `externalOrderId` |
| `Order::fromIngressSnapshot(...)` | фабрика без `checkout_id` |

Уникальность: `(partner_code, external_order_id)` в `ORD_orders`.
