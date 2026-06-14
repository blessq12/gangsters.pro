# Delivery — слой домена

Ядро BC: сущности, value objects и контракт доступа к данным. Без Laravel, без HTTP, без Filament.

## Сущности

| Элемент | Смысл |
|---------|--------|
| `DeliveryConfiguration` | Публичная конфигурация: тарифы, срок, адрес кухни, координаты, GeoJSON зоны |

## Value objects

| VO | Смысл |
|----|--------|
| `KitchenAddress` | Адрес кухни: `city`, `street`, `house`, `comment`, `searchLine` |

## Перечисления

В домене Delivery **нет** enum-классов.

## Репозитории (порты)

| Порт | Ответственность |
|------|-----------------|
| `DeliveryConfigurationRepository` | Публичная конфигурация доставки (`findPublic`) |

Константа `SINGLETON_ID = 1` — единственная строка настроек.

Домен **не знает** про таблицу `DLV_configuration` и Eloquent — только интерфейс и чистые типы.

## Инварианты (семантика)

- Конфигурация — **одна** на сервис (singleton).
- GeoJSON зоны в маппере принимается только с `type`: `Polygon` или `MultiPolygon`; иначе в домене `null`.
- Все денежные поля в домене — целые **копейки** (`?int`), nullable.
