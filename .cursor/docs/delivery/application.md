# Delivery — слой приложения

Оркестрация сценариев **внутри BC Delivery**: читает домен через порт репозитория, собирает контракт для витрины.

## Активные сценарии Delivery BC

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

### `zone.kitchen_address`

| Поле API | Filament | Смысл |
|----------|----------|--------|
| `city`, `street`, `house`, `comment` | Город / улица / дом / комментарий | Структурированный адрес кухни для текстов на витрине |
| `search_line` | **Адрес для поиска на карте** (`kitchen_address`) | Только Filament: геокодер редактора зоны; **не** выводится на витрине (покрытие — GeoJSON) |

## Связанный код в других BC (не Application/Delivery)

Логика, **зависящая** от конфига Delivery, но живущая вне `app/Application/Delivery/`:

| Класс | BC | Роль |
|-------|-----|------|
| `PrepareCheckoutDeliveryAddress` | Checkout | Геокодирование адреса курьера через `DeliveryAddressGeocoderPort` + city из конфига |
| `SetCheckoutDeliveryUseCase` | Checkout | Вызывает `PrepareCheckoutDeliveryAddress` перед сохранением `DeliverySnapshot` |
| `EvaluateDeliveryBenefits` | Promotion | `delivery_pricing`, `in_zone` через `PromotionDeliveryPricingPort` |
| `EvaluatePromotionBenefits` | Promotion | Оркестратор benefits + delivery pricing |

## Админские мутации

Сейчас **не** проходят через Application Delivery. Операторское сохранение выполняет Filament напрямую через Eloquent-модель `DLV_Configuration` (см. `filament.md`).

## DTO / Presenter / Ports

В BC Delivery **нет** DTO, Presenter и Command-сценариев — один read use case.

Порт геокодирования объявлен в Domain, реализован в Infrastructure; Application Delivery его **не** вызывает.
