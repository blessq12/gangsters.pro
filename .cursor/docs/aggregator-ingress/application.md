# AggregatorIngress — Application

## Use case

| Класс | Назначение |
|-------|------------|
| `ReceiveExternalOrderUseCase` | Единый pipeline приёма заказа от любого партнёра |

## DTO

| DTO | Поля |
|-----|------|
| `ReceiveExternalOrderDto` | `partnerCode`, `apiKey`, `payload` (array) |

## Порты (Strategy)

| Интерфейс | Роль |
|-----------|------|
| `IngressPartnerAdapter` | `partnerCode()`, `extractExternalOrderId()`, `map()` |
| `IngressPartnerAuthenticator` | `authenticate(partnerCode, apiKey)` |

## Сервисы

| Класс | Роль |
|-------|------|
| `IngressPartnerAdapterRegistry` | Резолв адаптера по `partnerCode` |

## ACL

| Класс | Направление |
|-------|-------------|
| `IngressMappedOrderToCreateOrderMapper` | `IngressMappedOrder` + resolved lines → `CreateOrderFromIngressDto` |

## Зависимость от Order BC

| Класс | BC | Назначение |
|-------|-----|------------|
| `CreateOrderFromIngressUseCase` | Order | Write: идемпотентное создание по `(partner, external_order_id)` |
| `OrderPresenter` | Order | Ответ API и audit |

## Pipeline (Template Method)

1. `authenticate`
2. `adapterRegistry.resolve(partner)`
3. `adapter.extractExternalOrderId` → проверка существующего заказа
4. `adapter.map`
5. `catalogBindings.resolve` для каждой строки
6. `IngressMappedOrderToCreateOrderMapper`
7. `CreateOrderFromIngressUseCase`
8. `audit.record`

## Ответ use case

```json
{
  "order_id": 42,
  "status": "accepted",
  "order": { /* OrderPresenter */ }
}
```

## Подключение нового партнёра

1. Класс `*IngressPartnerAdapter` в `Infrastructure/AggregatorIngress/Adapter/`
2. Запись в `config/ingress.php` (`enabled`, `api_key`)
3. Регистрация в `AggregatorIngressServiceProvider`
4. SKU bindings в `ING_partner_sku_bindings`
5. Контракт в [partners.md](partners.md)
6. Unit-тест в `tests/Unit/AggregatorIngress/`
