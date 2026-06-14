# Promotion — обзор BC

**Роль:** операционные правила коммерческих выгод — пороги подарка, политика стоимости доставки от суммы корзины и зоны. Только **конфигурация**; применение к корзине/checkout — позже через Application-слой Checkout.

## Семантика

| Термин | Смысл |
|--------|--------|
| **PromotionPolicy** | Единая конфигурация BC (singleton id=1) |
| **Правило подарка** | Условие «ролл в подарок» по способу получения и сумме корзины |
| **Политика доставки** | Как считать доставку от порога суммы и положения адреса относительно зоны |
| **Кандидат подарка** | Товар с `meta_gift_candidate` в Catalog — **не** хранится в Promotion |

## Правила (текущий бизнес-контракт)

Деньги в домене и БД — **копейки** (`int`). Способ получения — как в Checkout: `pickup` \| `courier`.

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
| Порт `PromotionPolicyRepository` | Список товаров-кандидатов — Catalog BC |
| | Расчёт `benefitsProgress` / `promoState`, строка подарка в корзине — Checkout (+ Application ACL) |

**Не путать:** `MarketingContent\Entity\Promotion` и `Promotion` BC — разные bounded contexts, разные таблицы (`MKT_*` vs `PRM_*`).

## Хранение

Таблица `PRM_configuration` — singleton (id=1). Нормализованные колонки под текущий набор правил (без generic rules engine на старте).

## Пути в коде (целевые)

| Слой | Promotion |
|------|-----------|
| Domain | `app/Domain/Promotion/` |
| Application | `app/Application/Promotion/` — `EvaluatePromotionBenefits` (расчёт benefits) |
| Infrastructure | `app/Infrastructure/Promotion/` |
| HTTP | позже `GET /api/promotion` или вложение в checkout snapshot |
| Filament | `app/Filament/Promotion/` — hub настроек акций |
| SPA | `cartStore.benefitsProgress`, `promoState` — потребитель расчёта, не BC Promotion |

## Аудит (состояние)

### Сейчас

- Только проектирование (этот документ + domain/infrastructure).

### Техдолг (после появления хранения)

1. `GetPromotionPolicyUseCase` + публичный read API (если нужен отдельный endpoint).
2. `EvaluateCheckoutBenefitsUseCase` в Checkout Application: Promotion + Delivery + Catalog.
3. Filament `ManagePromotion` (singleton).
4. Подключение фронта: сейчас `benefitsProgress` / `promoState` без бекенда.
5. Граница с `DLV_configuration.min_order_amount_kopecks` — см. domain.md (единый источник порога 1000 ₽).
