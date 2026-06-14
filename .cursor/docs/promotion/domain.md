# Promotion — слой домена

Ядро BC: конфигурация коммерческих правил. Без Laravel, HTTP, Filament, без знания Catalog/Delivery implementation.

## Паттерны (GoF)

| Паттерн | Применение |
|---------|------------|
| **Singleton** (семантика) | Одна `PromotionPolicy` на сервис (`SINGLETON_ID = 1`) |
| **Value Object** | `GiftBenefitRule`, `DeliveryBenefitPolicy` — неизменяемые куски политики |
| **Strategy** (задел) | Типы выгод (`GiftBenefitType`) и режимы тарифа (`DeliveryFeeMode`) — разные алгоритмы применения в Application |
| **Repository** | `PromotionPolicyRepository` — доступ к конфигурации |

## Агрегат

| Элемент | Смысл |
|---------|--------|
| `PromotionPolicy` | Aggregate Root: id, коллекция правил подарка, политика доставки |

Фабрики:

- `PromotionPolicy::restore(...)` — гидратация из персистентности.
- Создание через Application/Filament — отдельно, инварианты на VO.

## Value objects

### `GiftBenefitRule`

| Поле | Тип | Смысл |
|------|-----|--------|
| `orderChannel` | `PromotionOrderChannel` | `pickup` \| `courier` |
| `minOrderAmountKopecks` | `int` | Порог; подарок при **строго большей** сумме корзины |
| `benefitType` | `GiftBenefitType` | Пока только `FreeRollGift` |
| `isActive` | `bool` | Включено ли правило |

Инвариант: `minOrderAmountKopecks > 0`. Для пары каналов допускаются разные пороги (1000 / 1800 ₽).

### `DeliveryBenefitPolicy`

| Поле | Тип | Смысл |
|------|-----|--------|
| `freeDeliveryThresholdKopecks` | `int` | От этой суммы **включительно** действуют правила «бесплатно в зоне» / «+надбавка вне зоны» |
| `outsideZoneSurchargeKopecks` | `int` | Надбавка к базовому тарифу вне зоны при сумме ≥ порога (200 ₽) |
| `belowThresholdFeeMode` | `DeliveryFeeMode` | Пока `BaseTariff` — брать `delivery_fee_kopecks` из Delivery port |
| `inZoneAtThresholdFeeMode` | `DeliveryFeeMode` | Пока `Free` |
| `outsideZoneAtThresholdFeeMode` | `DeliveryFeeMode` | Пока `BasePlusSurcharge` |
| `isActive` | `bool` | Включена ли политика |

Инварианты: порог и надбавка ≥ 0; при `isActive = false` Application не применяет политику (fallback на чистый Delivery).

## Перечисления

### `PromotionOrderChannel`

- `Pickup`
- `Courier`

Маппинг на Checkout `DeliveryMethod` — в Application ACL (те же строки `pickup` / `courier`).

### `GiftBenefitType`

- `FreeRollGift`

### `DeliveryFeeMode`

| Значение | Смысл при расчёте (Application) |
|----------|-----------------------------------|
| `BaseTariff` | Стоимость = `DeliveryConfiguration::deliveryFeeKopecks()` (или `outsideZoneDeliveryFeeKopecks` если вне зоны и сумма < порога — по матрице) |
| `Free` | 0 |
| `BasePlusSurcharge` | базовый тариф + `outsideZoneSurchargeKopecks` из политики Promotion |

### `DeliveryZoneScope` (для будущих правил, не в VO сейчас)

- `Any`, `InZone`, `OutsideZone` — используется в Application при сопоставлении адреса с GeoJSON из Delivery.

## Репозиторий (порт)

| Порт | Метод | Ответственность |
|------|-------|-----------------|
| `PromotionPolicyRepository` | `find(): ?PromotionPolicy` | Текущая конфигурация (singleton) |
| | `save(PromotionPolicy): void` | Запись из Filament (позже) |

Константа `SINGLETON_ID = 1`.

Домен **не знает** про `PRM_configuration` и Eloquent.

## Разделение с Delivery BC

| Данные | Владелец |
|--------|----------|
| `delivery_zone_geojson`, координаты кухни | Delivery |
| `delivery_fee_kopecks`, `outside_zone_delivery_fee_kopecks` (базовые тарифы) | Delivery |
| `min_order_amount_kopecks` в `DLV_configuration` | Delivery (минимальный заказ для **сервиса**; может совпадать с порогом акции) |
| Порог бесплатной доставки в зоне (1000 ₽) | **Promotion** |
| Надбавка +200 ₽ вне зоны при сумме ≥ 1000 ₽ | **Promotion** |

Рекомендация: порог **1000 ₽** для доставки хранить **только в Promotion**; в Delivery оставить базовые тарифы и зону. При внедрении синхронизировать Filament-подсказки, чтобы оператор не дублировал смысл в двух формах.

## Что домен **не** делает

- Не проверяет точку в полигоне (Delivery / shared geo helper в Infrastructure).
- Не загружает кандидатов подарка (Catalog port в Checkout Application).
- Не пишет `kind: gift` в корзину (Checkout).
- Не публикует события (конфигурация статична до save из админки).

## Зависимости BC (матрица)

```
Promotion  →  (нет исходящих domain-зависимостей)

Checkout Application  →  PromotionPolicyRepository (read)
Checkout Application  →  DeliveryConfigurationRepository (read)
Checkout Application  →  Catalog (gift candidates, read)
```
