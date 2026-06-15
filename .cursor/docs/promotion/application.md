# Promotion — слой приложения

## Use cases (read)

| Use case | Потребитель | Порт |
|----------|-------------|------|
| `GetPromotionPolicyUseCase` | Storefront bootstrap, Filament | `PromotionPolicyRepository::find()` |
| `EvaluatePromotionBenefits` | OrderDraft pipeline | policy + delivery pricing port + catalog meta |

## Use cases (write)

| Use case | Потребитель | Действие |
|----------|-------------|----------|
| `UpdatePromotionPolicyUseCase` | Filament save | Валидация VO → `save()` |

## ACL: расчёт выгод

Promotion **не знает** OrderDraft. Вызов:

```
Order/Application/OrderDraft/EvaluateOrderDraftBenefits
  → OrderDraftBenefitsInputMapper
  → EvaluatePromotionBenefits(PromotionBenefitsInput)
```

Sync строк корзины (gift/complement) — **OrderDraft** (`ApplyGiftBenefitLines`, `ApplyComplementBenefitLines`), не Promotion.

### `PromotionBenefitsInput`

| Поле | Источник |
|------|----------|
| `cartTotalKopecks` | OrderDraft cart |
| `orderChannel` | delivery.method |
| `deliveryPoint` | geocoded lat/lng |
| `cartLines` | для gift line detection |

### Порты

- `PromotionPolicyRepository`
- `PromotionDeliveryPricingPort` → Delivery config + PRM thresholds
- Catalog candidates — через Order ports, не напрямую из Promotion Application

### Выход

`PromotionBenefitsResult` → `OrderDraftPresenter` → SPA (`benefits_progress`, `delivery_pricing`, `promo_state`).

## События

Доменных событий Promotion нет.
