# Promotion — слой приложения

Use case'ов **нет**. Ниже — целевые команды/запросы на этап внедрения.

## Запросы (read)

| Use case | Потребитель | Порт |
|----------|-------------|------|
| `GetPromotionPolicyUseCase` | Filament preload, опционально `GET /api/promotion` | `PromotionPolicyRepository::find()` |

Ответ DTO — плоский snapshot порогов для API или формы.

## Команды (write)

| Use case | Потребитель | Действие |
|----------|-------------|----------|
| `UpdatePromotionPolicyUseCase` | Filament save | Валидация VO → `save()` |

Мутации **только** из админки; публичный API без write.

## ACL: расчёт выгод (не в BC Promotion)

Логика применения живёт в **Checkout Application** (или отдельном application service `CheckoutBenefitsEvaluator`), чтобы Promotion оставался storage-only.

### `EvaluateCheckoutBenefitsInput` (черновик)

| Поле | Источник |
|------|----------|
| `cartTotalKopecks` | слепок корзины Checkout |
| `orderChannel` | блок доставки checkout (`pickup` \| `courier`) |
| `deliveryAddress` | координаты / point-in-polygon |
| `cartLines` | для определения уже выбранного подарка (`kind: gift`) |

### Порты на чтение

- `PromotionPolicyRepository`
- `DeliveryConfigurationRepository` — базовые тарифы + GeoJSON
- `CatalogGiftCandidatesPort` — товары с `meta_gift_candidate`

### Выход (контракт под SPA)

```json
{
  "benefitsProgress": {
    "delivery": {
      "isActive": true,
      "isReached": false,
      "thresholdKopecks": 100000,
      "currentKopecks": 75000,
      "remainingKopecks": 25000
    },
    "gift": {
      "isActive": true,
      "isReached": false,
      "thresholdKopecks": 180000,
      "currentKopecks": 75000,
      "remainingKopecks": 105000
    }
  },
  "promoState": {
    "gift_promotion": {
      "eligible": false,
      "phase": "below_threshold",
      "selected_product_id": null,
      "candidates": []
    }
  },
  "deliveryFeeKopecks": 35000
}
```

`thresholdKopecks` для gift берётся из активного `GiftBenefitRule` по текущему `orderChannel`. Для pickup без выбранного способа доставки — дефолт `pickup` или `isActive: false` (решение при внедрении в Checkout).

### Матрица расчёта доставки (Application)

```
если orderChannel != courier → deliveryFee = 0

если !deliveryBenefitActive
  → fee из Delivery (legacy)

если cartTotal < freeDeliveryThreshold
  → fee = base (in zone: delivery_fee_kopecks; outside: outside_zone_delivery_fee_kopecks)

если cartTotal >= freeDeliveryThreshold && inZone
  → fee = 0

если cartTotal >= freeDeliveryThreshold && !inZone
  → fee = delivery_fee_kopecks + outsideZoneSurchargeKopecks
```

Базовые `delivery_fee_kopecks` / `outside_zone_delivery_fee_kopecks` — из Delivery, не из Promotion.

### Матрица подарка

```
rule = giftRule для orderChannel
если !rule.isActive → gift inactive
если cartTotal <= rule.minOrderAmountKopecks → below threshold
иначе eligible, phase select_gift если нет line kind=gift
candidates = catalog gift candidates
```

## События

Доменных событий Promotion нет. Инвалидация кэша на витрине — по `updated_at` конфигурации (если понадобится).
