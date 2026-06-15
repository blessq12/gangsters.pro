# Promotion — потоки

## 1. Bootstrap (read policy)

```
SPA app mount
  → GET /api/storefront/bootstrap
  → GetPromotionPolicyUseCase
  → promotion: { gift, complement, delivery_benefit }
```

Пороги подарка и бесплатной доставки — **единый источник PRM**.

## 2. OrderDraft preview / place (evaluate benefits)

```
SPA local draft
  → POST /api/order-drafts/preview | POST /api/orders
  → ProcessOrderDraftPipeline
  → EvaluateOrderDraftBenefits
  → OrderDraftBenefitsInputMapper → PromotionBenefitsInput
  → EvaluatePromotionBenefits
  → ApplyGiftBenefitLines / ApplyComplementBenefitLines (sync cart lines)
  → OrderDraftPresenter: benefits_progress, delivery_pricing
```

## 3. Выбор подарка (SPA)

```
GiftSelectionModal
  → checkoutStore.setPromotionGift(productId)
  → promotions.freeRollGiftProductId в sessionStorage
  → POST /api/order-drafts/preview
```

## 4. Legacy read (опционально)

Отдельный `GET /api/promotion` не обязателен — policy уже в bootstrap.

## Разделение ответственности

| Контур | Promotion | OrderDraft |
|--------|-----------|------------|
| Хранение правил | `PRM_configuration` | — |
| Расчёт eligibility | `EvaluatePromotionBenefits` | orchestration |
| Sync gift/complement lines | — | `Apply*BenefitLines` |
| Geocode + in_zone | — | `PrepareOrderDraftDeliveryAddress` + pricing port |
