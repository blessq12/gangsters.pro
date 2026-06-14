# Promotion — флоу

## 1. Оператор настраивает акции (будущее)

```
Браузер → /admin/promotion
    → ManagePromotion (Filament)
    → firstOrCreate PRM_configuration id=1
    → форма порогов подарка / доставки
    → save → Eloquent PRM_configuration
```

Базовый тариф и полигон зоны — в `/admin/delivery`.

## 2. Checkout пересчитывает выгоды (будущее)

```
SPA PATCH /api/checkout/{id}/cart | delivery
    → Checkout use case
    → EvaluateCheckoutBenefits (Application)
        → PromotionPolicyRepository::find()
        → DeliveryConfigurationRepository::findPublic()
        → CatalogGiftCandidatesPort
    → ответ: cart snapshot + benefitsProgress + promoState + deliveryFeeKopecks
```

Promotion BC в этом флоу **только читается**.

## 3. Клиент выбирает подарок (уже частично на фронте)

```
SPA GiftSelectionModal
    → checkoutStore.setPromotionGift(productId)
    → PATCH cart line { kind: "gift" }
```

Правило eligibility сверяется с `PromotionPolicy` на сервере при следующем пересчёте; хранение выбора — в слепке корзины Checkout, не в PRM.

## 4. Публичный read (опционально)

```
SPA → GET /api/promotion
    → GetPromotionPolicyUseCase
    → JSON порогов (без кандидатов каталога)
```

Можно не выделять endpoint, если пороги всегда приходят в ответе checkout.

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Filament | Eloquent / use case | `UpdatePromotionPolicyUseCase` или прямой save (как Delivery сейчас) |
| Checkout API | `PromotionPolicyRepository` через evaluator | — |
| Публичный API | `GetPromotionPolicyUseCase` | — |
