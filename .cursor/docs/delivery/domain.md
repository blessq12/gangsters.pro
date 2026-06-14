# Delivery — слой домена

Ядро BC: сущности, value objects, порты доступа к данным и геокодированию. Без Laravel, без HTTP, без Filament.

## Сущности

| Элемент | Смысл |
|---------|--------|
| `DeliveryConfiguration` | Публичная конфигурация: тарифы (`minOrderAmountKopecks`, `deliveryFeeKopecks`, `outsideZoneDeliveryFeeKopecks`), срок, `KitchenAddress`, координаты кухни, GeoJSON зоны |

Файл: `app/Domain/Delivery/Entity/DeliveryConfiguration.php`.

## Value objects

| VO | Смысл |
|----|--------|
| `KitchenAddress` | Адрес кухни: `city`, `street`, `house`, `comment`, `searchLine` |

Файл: `app/Domain/Delivery/ValueObject/KitchenAddress.php`.

## Перечисления

В домене Delivery **нет** enum-классов.

Способ доставки (`courier` \| `pickup`) — shared enum `App\Shared\Enum\DeliveryMethod` (используется Checkout / Promotion, не Delivery BC).

## Порты

### Репозиторий

| Порт | Ответственность |
|------|-----------------|
| `DeliveryConfigurationRepository` | Публичная конфигурация доставки (`findPublic`) |

Константа `SINGLETON_ID = 1` — единственная строка настроек.

Файл: `app/Domain/Delivery/Repository/DeliveryConfigurationRepository.php`.

### Геокодирование

| Порт | Ответственность |
|------|-----------------|
| `DeliveryAddressGeocoderPort` | `geocode(street, house, city) → {latitude, longitude} \| null` |

Файл: `app/Domain/Delivery/Port/DeliveryAddressGeocoderPort.php`.

**Потребитель:** `Application\Checkout\Services\PrepareCheckoutDeliveryAddress` — не use case Delivery BC.  
City для запроса берётся из `kitchen_address` конфигурации.

Домен **не знает** про Yandex API, Eloquent и таблицу `DLV_configuration` — только интерфейсы и чистые типы.

## Инварианты (семантика)

- Конфигурация — **одна** на сервис (singleton).
- GeoJSON зоны в маппере принимается только с `type`: `Polygon` или `MultiPolygon`; иначе в домене `null`.
- Все денежные поля в домене — целые **копейки** (`?int`), nullable.
- `outsideZoneDeliveryFeeKopecks` — отдельный тариф за пределами зоны; при `null` в pricing adapter fallback на `deliveryFeeKopecks`.

## Чего нет в домене Delivery

- Порта расчёта стоимости доставки — pricing живёт в Promotion (`PromotionDeliveryPricingPort`).
- Событий, команд записи, валидации адреса курьера.
- Типов checkout/order (`DeliveryAddress`, `DeliverySnapshot` — в Checkout / Order BC).
