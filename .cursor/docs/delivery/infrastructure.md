# Delivery — слой инфраструктуры

Реализация доменного порта и персистентность. Единственное место, где живут Eloquent и таблица `DLV_*`.

## Модели (`DLV_*`)

| Модель | Таблица | Смысл |
|--------|---------|--------|
| `DLV_Configuration` | `DLV_configuration` | Singleton-конфигурация доставки |

### Колонки `DLV_configuration`

| Колонка | Смысл |
|---------|--------|
| `min_order_amount_kopecks` | Минимальная сумма заказа |
| `delivery_fee_kopecks` | Стоимость доставки в зоне |
| `outside_zone_delivery_fee_kopecks` | Стоимость доставки за пределами зоны |
| `average_delivery_time_minutes` | Среднее время доставки (мин) |
| `kitchen_city`, `kitchen_street`, `kitchen_house`, `kitchen_address_comment` | Структурированный адрес кухни |
| `kitchen_address` | Строка для геокодера на карте |
| `kitchen_latitude`, `kitchen_longitude` | Координаты кухни |
| `delivery_zone_geojson` | Полигон зоны (JSON) |

## Репозитории

| Класс | Порт |
|-------|------|
| `EloquentDeliveryConfigurationRepository` | `DeliveryConfigurationRepository` |

`findPublic()` читает строку с `id = SINGLETON_ID` (1).

Маппинг строки БД ↔ домен — `DeliveryConfigurationMapper`.

## Композиция

`DeliveryServiceProvider` регистрирует привязку порта к Eloquent-реализации (`config/app.php`).

## Сидирование

`Database\Seeders\DeliverySeeder` — демо-конфигурация (Томск, тарифы, полигон). Вызывается из `DatabaseSeeder`.
