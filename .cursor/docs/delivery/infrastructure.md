# Delivery — слой инфраструктуры

Реализация доменных портов и персистентность. Единственное место в Delivery BC, где живут Eloquent и таблица `DLV_*`.

## Модели (`DLV_*`)

| Модель | Таблица | Смысл |
|--------|---------|--------|
| `DLV_Configuration` | `DLV_configuration` | Singleton-конфигурация доставки |

Файл: `app/Infrastructure/Delivery/Model/DLV_Configuration.php`.

### Колонки `DLV_configuration`

| Колонка | Смысл |
|---------|--------|
| `min_order_amount_kopecks` | Минимальная сумма заказа (порог бесплатной доставки в зоне) |
| `delivery_fee_kopecks` | Стоимость доставки в зоне |
| `outside_zone_delivery_fee_kopecks` | Стоимость доставки за пределами зоны |
| `average_delivery_time_minutes` | Среднее время доставки (мин) |
| `kitchen_city`, `kitchen_street`, `kitchen_house`, `kitchen_address_comment` | Структурированный адрес кухни |
| `kitchen_address` | Строка для геокодера на карте (`search_line`) |
| `kitchen_latitude`, `kitchen_longitude` | Координаты кухни |
| `delivery_zone_geojson` | Полигон зоны (JSON) |

## Репозитории

| Класс | Порт |
|-------|------|
| `EloquentDeliveryConfigurationRepository` | `DeliveryConfigurationRepository` |

`findPublic()` читает строку с `id = SINGLETON_ID` (1).

Маппинг строки БД ↔ домен — `DeliveryConfigurationMapper` (GeoJSON: только `Polygon` / `MultiPolygon`).

## Адаптеры портов

| Класс | Порт | Смысл |
|-------|------|--------|
| `YandexDeliveryAddressGeocoderAdapter` | `DeliveryAddressGeocoderPort` | HTTP → Yandex Geocoder API (`config/services.yandex_maps.geocoder_api_key`), timeout 5s |

Файл: `app/Infrastructure/Delivery/Port/YandexDeliveryAddressGeocoderAdapter.php`.

При пустом API key, неуспешном HTTP или пустом ответе — `null` (без исключений).

## Связанная инфраструктура (другие BC)

| Класс | BC | Смысл |
|-------|-----|--------|
| `PromotionDeliveryPricingAdapter` | Promotion | Реализует `PromotionDeliveryPricingPort`, читает `DeliveryConfigurationRepository`, использует `App\Shared\Geo\PointInGeoJsonZone` |

Регистрация: `PromotionServiceProvider`, не `DeliveryServiceProvider`.

### Логика `PromotionDeliveryPricingAdapter::resolveDeliveryFeeKopecks`

| Условие | Fee |
|---------|-----|
| `pickup` | 0 |
| `courier`, in zone, сумма ≥ min | 0 |
| `courier`, in zone, сумма < min | `delivery_fee_kopecks` |
| `courier`, out zone, сумма ≥ min | `outside_zone_delivery_fee_kopecks` |
| `courier`, out zone, сумма < min | `delivery_fee_kopecks` + `outside_zone_delivery_fee_kopecks` |

`resolveFreeDeliveryThresholdKopecks()` = `min_order_amount_kopecks` из конфига.

## Композиция

`DeliveryServiceProvider` регистрирует:

```php
DeliveryConfigurationRepository → EloquentDeliveryConfigurationRepository
DeliveryAddressGeocoderPort → YandexDeliveryAddressGeocoderAdapter
```

Зарегистрирован в `config/app.php`.

## Ключи Яндекс.Карт

`config/services.php` → `yandex_maps.api_key` (карты), `yandex_maps.geocoder_api_key` (геокодер; fallback на maps key в config).

SPA получает maps key через `SitePublicConfigPresenter` → `window.__SITE__.yandexMapsApiKey`.

## Сидирование

`Database\Seeders\DeliverySeeder` — демо-конфигурация (Томск, тарифы, полигон). Вызывается из `DatabaseSeeder`.

## Тесты

| Файл | Покрытие |
|------|----------|
| `tests/Unit/Promotion/PromotionDeliveryPricingAdapterTest.php` | Тарифная логика in/out zone (5 unit-тестов) |
| `tests/Feature/PlaceOrderTest.php` | courier delivery in/out zone через place |

Тестов слоя `Infrastructure/Delivery` (repository, mapper, geocoder) **нет**.
