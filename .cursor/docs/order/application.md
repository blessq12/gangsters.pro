# Order — Application

## Use cases (агрегат Order)

| Класс | Назначение |
|-------|------------|
| `CreateOrderUseCase` | Persist заказа из `CreateOrderDto`; идемпотентен по `clientRequestId` |
| `CreateOrderFromIngressUseCase` | Write (агрегатор) |
| `ListClientOrdersUseCase` | Read: список заказов клиента |
| `GetOrderUseCase` | Read: детали заказа клиента с ACL |

## OrderDraft (сайт, in-memory)

Расположение: `app/Application/Order/OrderDraft/`.

| Класс | Назначение |
|-------|------------|
| `PreviewOrderDraftUseCase` | Stateless preview → `OrderDraftPresenter` |
| `PlaceOrderUseCase` | Authoritative place → `CreateOrderUseCase` |
| `BuildOrderDraftFromInput` | JSON → `OrderDraft` entity; nullable координаты адреса |
| `ProcessOrderDraftPipeline` | Template Method: complement/gift sync, geocode, validate |
| `ApplyGiftBenefitLines` | Sync gift line |
| `ApplyComplementBenefitLines` | Sync complement lines |
| `PrepareOrderDraftDeliveryAddress` | Geocode courier address (пропускает null, `""`, `(0,0)`) |
| `EvaluateOrderDraftBenefits` | → `EvaluatePromotionBenefits` |

### Pipeline `ProcessOrderDraftPipeline`

```
1. ApplyComplementBenefitLines::sync
2. ApplyGiftBenefitLines::sync
3. PrepareOrderDraftDeliveryAddress (если courier)
4. [forPlace] assertReadyForPlace + assertValidForPlace (gift)
```

Preview останавливается после шага 3; place выполняет шаг 4.

`BuildOrderDraftFromInput::build()` вызывает `setClient` / `setDelivery` / `setPayment` **только** для не-null блоков — частичный draft на ранних шагах визарда допустим.

### Geocode адреса

`PrepareOrderDraftDeliveryAddress`:
- если lat/lng отсутствуют, пустые или `(0,0)` → Yandex geocoder (street + house + city из DLV);
- иначе координаты клиента используются as-is для `in_zone`.

## DTO

| DTO | Поля |
|-----|------|
| `CreateOrderDto` | clientRequestId, cart, client, delivery, payment, createdAt |
| `OrderDraftInput` | cartLines, selectedGiftProductId, client?, delivery?, payment? |
| `PlaceOrderInput` | clientRequestId + OrderDraftInput |
| `CreateOrderFromIngressDto` | partnerCode, externalOrderId, … |

## ACL / Mappers

| Mapper | Направление |
|--------|-------------|
| `OrderDraftToCreateOrderMapper` | OrderDraft VO → `CreateOrderDto` |
| `OrderDraftBenefitsInputMapper` | OrderDraft → `PromotionBenefitsInput` |
| `IngressMappedOrderToCreateOrderMapper` | AggregatorIngress → ingress DTO |

~~`CheckoutConfirmedOrderSnapshotMapper`~~ и ~~`OnCheckoutConfirmed`~~ **удалены**.

## Presenter

`OrderDraftPresenter` — контракт preview (совместим с прежним checkout presenter: `cart`, `benefits_progress`, `delivery_pricing`, `wizard`, `order_preview`).

`OrderPresenter::present(Order)` — в ответе также `client_request_id` (alias `checkout_id` в БД).

## Порты Order (Catalog ACL)

`app/Domain/Order/Port/` — бывшие Checkout ports:

- `CatalogPricingPort`
- `CatalogGiftCandidatesPort`
- `CatalogComplementSetCandidatesPort`
- `CatalogRollMetaPort`
- `ClientProfilePort`

Биндинги: `OrderServiceProvider`.
