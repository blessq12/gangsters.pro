# OrderAccountingExport — события

## Входящие

| Событие | Источник | Обработчик |
|---------|----------|------------|
| `OrderCreated` | Order BC | `OnOrderCreated` → fan-out в системы учёта |

Подписка: `OrderAccountingExportServiceProvider::boot()`.

```php
Event::listen(OrderCreated::class, [OnOrderCreated::class, 'handle']);
```

## `OrderCreated` (Order Domain)

Класс: `App\Domain\Order\Event\OrderCreated`.

| Поле | Тип | Смысл |
|------|-----|--------|
| `orderId` | `OrderId` | PK после `save` |
| `source` | `OrderSource` | `site` \| `aggregator` |
| `checkoutId` | `?string` | для сайта |
| `aggregatorReference` | `?OrderAggregatorReference` | для агрегатора |
| `cart` | `OrderCartSnapshot` | строки заказа |
| `client` | `OrderClientSnapshot` | гость или registered |
| `delivery` | `OrderDeliverySnapshot` | courier / pickup |
| `payment` | `OrderPaymentSnapshot` | способ оплаты |
| `occurredAt` | `DateTimeImmutable` | время создания |

Фабрика: `OrderCreated::fromOrder(Order $order)` — только если `hasId()`.

### Где диспатчится

| Use case | Условие |
|----------|---------|
| `CreateOrderUseCase` | после `save`, если заказ новый |
| `CreateOrderFromIngressUseCase` | после `save`, если заказ новый |

```php
Event::dispatch(OrderCreated::fromOrder($order));
```

## Исходящие

BC **не публикует** доменных событий после экспорта (техдолг: `OrderExported`, `OrderExportFailed` для мониторинга).

## Audit (не доменное событие)

`ExportAttemptRepository::record` — лог в `OAE_export_attempts`:

| status | Смысл |
|--------|--------|
| `success` | заказ принят внешней системой |
| `failed` | ошибка маппинга или API |

Поля: `order_id`, `system_code`, `attempt`, `external_reference`, `error_message`, `created_at`.

## Другие потенциальные подписчики `OrderCreated`

Тот же хук могут использовать (пока не реализовано):

- email / push подтверждение клиенту
- аналитика
- webhooks партнёрам

См. [Order events](../order/events.md).
