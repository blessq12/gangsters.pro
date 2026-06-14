# Promotion — инфраструктура

Персистентность singleton-конфигурации. Use case'ов и HTTP пока нет.

## Таблица `PRM_configuration`

Одна строка `id = 1`. Суммы — `unsignedBigInteger` в копейках.

| Колонка | Nullable | Default (seed) | Смысл |
|---------|----------|----------------|--------|
| `id` | — | `1` | PK, singleton |
| `gift_pickup_min_order_kopecks` | да | `100000` | Порог подарка при самовывозе (> 1000 ₽) |
| `gift_courier_min_order_kopecks` | да | `180000` | Порог подарка при доставке (> 1800 ₽) |
| `gift_benefit_active` | нет | `true` | Вкл/выкл правила подарка |
| `delivery_free_threshold_kopecks` | да | `100000` | Порог бесплатной доставки в зоне (≥ 1000 ₽) |
| `delivery_outside_zone_surcharge_kopecks` | да | `20000` | Надбавка вне зоны (+200 ₽) |
| `delivery_benefit_active` | нет | `true` | Вкл/выкл политики доставки |
| `created_at`, `updated_at` | — | — | Laravel timestamps |

Почему колонки, а не JSON rules: текущий набор фиксированный (YAGNI); маппер явный, как у `DLV_configuration`. Расширение (третий тип акции) — миграция + новые VO, либо отдельная `PRM_rules` при появлении N правил.

## Eloquent-модель (целевая)

`App\Infrastructure\Promotion\Model\PRM_Configuration`

- `$table = 'PRM_configuration'`
- `$guarded = []` или явный `$fillable` под Filament
- Без soft deletes

## Mapper (целевой)

`PromotionPolicyMapper::toDomain(PRM_Configuration): PromotionPolicy`

Сборка:

```text
giftRules = [
  GiftBenefitRule(Pickup, gift_pickup_min_order_kopecks, FreeRollGift, gift_benefit_active),
  GiftBenefitRule(Courier, gift_courier_min_order_kopecks, FreeRollGift, gift_benefit_active),
]
deliveryBenefitPolicy = DeliveryBenefitPolicy(
  delivery_free_threshold_kopecks,
  delivery_outside_zone_surcharge_kopecks,
  BaseTariff, Free, BasePlusSurcharge,
  delivery_benefit_active,
)
```

`toPersistence(PromotionPolicy): array` — обратный маппинг для Filament save.

## Репозиторий (целевой)

`EloquentPromotionPolicyRepository` implements `PromotionPolicyRepository`

- `find()`: `PRM_Configuration::query()->find(SINGLETON_ID)` → mapper или `null`
- `save()`: `updateOrCreate(['id' => 1], ...)`

## Provider (целевой)

`PromotionServiceProvider`: bind `PromotionPolicyRepository` → `EloquentPromotionPolicyRepository`.

## Seed

`PromotionConfigurationSeeder` или блок в общем seeder: `firstOrCreate` id=1 с дефолтами из таблицы выше.

## Связь с Filament (задел)

`ManagePromotion` extends `EditRecord`, model `PRM_Configuration`, `mount` → `firstOrCreate` id=1.

Табы:

1. **Подарок** — два порога + toggle активности.
2. **Доставка** — порог бесплатной доставки, надбавка вне зоны + toggle.

Подсказка в форме: базовый тариф и зона редактируются в hub Delivery.
