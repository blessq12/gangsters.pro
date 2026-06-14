# Promotion — Filament

Singleton-страница настроек акций. Реализации пока нет.

## Hub

- Путь: `/admin/promotion`
- Класс: `app/Filament/Promotion/Resources/PromotionPolicyResource.php`
- Страница: `ManagePromotionPolicy`
- Модель: `PRM_Configuration` (Infrastructure)
- Паттерн: как `ManageDelivery` — `EditRecord` + `firstOrCreate` id=1

## Форма

### Секция «Подарок»

| Поле | Тип | Валидация |
|------|-----|-----------|
| `gift_benefit_active` | Toggle | — |
| `gift_pickup_min_order_kopecks` | Money input (₽ → копейки в save) | min 1 |
| `gift_courier_min_order_kopecks` | Money input | min 1 |

Helper text: кандидаты роллов помечаются в каталоге (`meta_gift_candidate`).

### Секция «Доставка»

| Поле | Тип | Валидация |
|------|-----|-----------|
| `delivery_benefit_active` | Toggle | — |
| `delivery_free_threshold_kopecks` | Money input | min 0 |
| `delivery_outside_zone_surcharge_kopecks` | Money input | min 0 |

Helper text: базовый тариф и зона — в разделе «Доставка».

## Read-only в других ресурсах

Checkout / Order Filament **не** редактируют PRM; при просмотре заказа подарок виден в слепке корзины (`kind: gift`).
