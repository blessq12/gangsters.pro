# Delivery — слой приложения

Оркестрация сценариев: читает домен через порт репозитория, собирает контракт для витрины.

## Активные сценарии

| Сценарий | Назначение |
|----------|------------|
| `GetDeliveryDataUseCase` | Публичные данные доставки для SPA |

Расположение: `app/Application/Delivery/useCases/GetDeliveryDataUseCase.php`.

## Что делает `GetDeliveryDataUseCase`

1. Загружает конфигурацию через `DeliveryConfigurationRepository::findPublic()`.
2. Если строки нет — возвращает `{ data: null }`.
3. Иначе маппит домен в JSON с блоками `settings` и `zone`.

Зависимость — только интерфейс Domain: `DeliveryConfigurationRepository`.

## Формат ответа

```json
{
  "data": {
    "settings": {
      "min_order_amount_kopecks": 150000,
      "delivery_fee_kopecks": 20000,
      "outside_zone_delivery_fee_kopecks": 50000,
      "average_delivery_time_minutes": 45
    },
    "zone": {
      "kitchen_address": {
        "city": "Томск",
        "street": "пр. Ленина",
        "house": "1",
        "comment": null,
        "search_line": "Томск, пр. Ленина, 1"
      },
      "kitchen_latitude": 56.48458,
      "kitchen_longitude": 84.94817,
      "delivery_zone_geojson": { "type": "Polygon", "coordinates": [...] }
    }
  }
}
```

## Админские мутации

Сейчас **не** проходят через Application Delivery. Операторское сохранение выполняет Filament напрямую через Eloquent-модель `DLV_Configuration` (см. `filament.md`).

## DTO / Presenter / Ports

Отдельных DTO, Presenter и Command-сценариев в BC **нет** — один read use case.
