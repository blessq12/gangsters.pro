# Delivery — потоки

## 1. Bootstrap / read config

```
SPA app mount
  → GET /api/storefront/bootstrap
  → GetDeliveryDataUseCase
  → delivery: { settings, zone }
```

Legacy: `GET /api/delivery` — тот же use case.

## 2. SPA: выбор способа доставки

```
DeliveryDockPanel / checkout wizard
  → local draft (checkoutStore.delivery)
  → без PATCH на сервер до preview
```

## 3. OrderDraft: geocode + pricing

```
POST /api/order-drafts/preview | POST /api/orders
  → ProcessOrderDraftPipeline
  → PrepareOrderDraftDeliveryAddress (courier)
      → DeliveryAddressGeocoderPort (Yandex)
      → lat/lng → in_zone (PointInGeoJsonZone)
  → EvaluateOrderDraftBenefits
      → PromotionDeliveryPricingPort
      → delivery_pricing in response
```

## 4. Place → Order snapshot

```
PlaceOrderUseCase
  → OrderDraftToCreateOrderMapper
  → OrderDeliverySnapshot в ORD_orders.delivery_snapshot
```

Order BC **не** вызывает Delivery при persist.

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | `GetDeliveryDataUseCase` | — |
| OrderDraft preview | geocode port + pricing via Promotion | — |
| Filament | Eloquent | Eloquent save |
