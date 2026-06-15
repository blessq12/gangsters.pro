# Promotion — обзор BC

**Роль:** операционные правила коммерческих выгод — пороги подарка, complement set, политика стоимости доставки от суммы корзины и зоны. Расчёт benefits — `EvaluatePromotionBenefits`; вызывается из **Order OrderDraft** через `OrderDraftBenefitsInputMapper`.

## Семантика

| Термин | Смысл |
|--------|--------|
| **PromotionPolicy** | Единая конфигурация BC (singleton id=1) |
| **Правило подарка** | Условие «ролл в подарок» по способу получения и сумме корзины |
| **Политика доставки** | Как считать доставку от порога суммы и положения адреса относительно зоны |
| **Кандидат подарка** | Товар с `meta_gift_candidate` в Catalog — **не** хранится в Promotion |

## Правила (текущий бизнес-контракт)

Деньги в домене и БД — **копейки** (`int`). Способ получения: `pickup` \| `courier`.

### Подарок (ролл)

| Способ | Порог корзины | Выгода |
|--------|---------------|--------|
| `pickup` | строго **> 1000 ₽** (100 000 коп.) | `free_roll_gift` |
| `courier` | строго **> 1800 ₽** (180 000 коп.) | `free_roll_gift` |

Выбор конкретного ролла — из каталога (`PRD_products.meta_gift_candidate = true`), не из Promotion.

### Доставка (только `courier`)

| Сумма корзины | Зона адреса | Стоимость доставки |
|---------------|-------------|-------------------|
| **< 1000 ₽** | любая | базовый тариф из Delivery BC |
| **≥ 1000 ₽** | в зоне | **0** (бесплатно) |
| **≥ 1000 ₽** | вне зоны | базовый тариф из Delivery BC **+ 200 ₽** (20 000 коп.) |

Геометрия зоны и базовый тариф — **Delivery BC** (`DLV_configuration`). Promotion хранит только **пороги и надбавку** акции.

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Хранение и чтение конфигурации правил | MarketingContent (`MKT_promotions`) — витринные карточки, не правила корзины |
| Доменные типы правил, singleton-агрегат | GeoJSON зоны, адрес кухни, `delivery_fee_kopecks` — Delivery BC |
| Порт `PromotionPolicyRepository` | Кандидаты подарка — Catalog BC (через Order ACL ports) |
| Порт `PromotionDeliveryPricingPort` | Тарифы доставки — Delivery BC; **порог бесплатной доставки из PRM**, не из `DLV.min_order_amount` |
| `BenefitProductCandidate` | DTO кандидата внутри Promotion |
| | Вызов из OrderDraft: `EvaluateOrderDraftBenefits` → `EvaluatePromotionBenefits` |

**Не путать:** `MarketingContent\Entity\Promotion` и `Promotion` BC — разные bounded contexts, разные таблицы (`MKT_*` vs `PRM_*`).

## Хранение

Таблица `PRM_configuration` — singleton (id=1). Нормализованные колонки под текущий набор правил (без generic rules engine на старте).

## Пути в коде (целевые)

| Слой | Promotion |
|------|-----------|
| Domain | `app/Domain/Promotion/` |
| Application | `app/Application/Promotion/` — `EvaluatePromotionBenefits`, `ResolveComplementSetEntitlement`, DTO `PromotionBenefitsInput` |
| Infrastructure | `app/Infrastructure/Promotion/` |
| HTTP | `GET /api/storefront/bootstrap` → блок `promotion`; legacy `GET /api/promotion` опционален |
| Filament | `app/Filament/Promotion/` — hub настроек акций |
| SPA | `storefrontStore.promotion`, `checkoutStore.benefitsProgress` / `orderPreview` из preview |

## Аудит (состояние)

### Сейчас

- Domain + Infrastructure + Application (benefits, complement entitlement).
- `GetPromotionPolicyUseCase` + `PromotionPolicyPresenter` — в bootstrap.
- OrderDraft pipeline вызывает Promotion на каждом preview/place.
- `PromotionDeliveryPricingAdapter` читает free-delivery threshold из `PromotionPolicy.deliveryBenefitPolicy`.

### Техдолг

1. Filament `ManagePromotion` (singleton) — доработки UX.
2. Отдельный `GET /api/promotion` (если нужен без полного bootstrap).
