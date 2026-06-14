# Delivery — флоу

Сквозные сценарии BC и связанные контуры.

## 1. Витрина читает настройки доставки (API BC)

```
SPA → GET /api/delivery
    → DeliveryController
    → GetDeliveryDataUseCase
    → DeliveryConfigurationRepository
    → Eloquent DLV_configuration + Mapper
    → JSON { data: { settings, zone } | null }
```

Если строки id=1 нет — `data: null`.

## 2. Витрина: SPA read model

```
MainLayout* / страницы / dock
    → deliveryStore.fetchAll()
    → deliveryService → GET /api/delivery
    → normalizeDeliveryData (deliveryMappers.js)
    → toDeliveryFactsView → UI
```

**Потребители:**

| Место | Composable / store |
|-------|-------------------|
| `/delivery` (`DeliveryPage`) | `useDeliveryReadModel` |
| `/contacts` (`ContactsPage`) | `useDeliveryReadModel` |
| Dock-панель доставки | `useDeliveryReadModel` |
| Мобильное меню, layout | `deliveryStore` (preload + facts) |

Утилиты отображения: `companyDeliveryFacts.js`, `companyDeliveryZoneMap.js`, `mountYandexDeliveryZoneReadonlyMap.js` (имена legacy, данные из Delivery API).

Ключ карт на витрине: `window.__SITE__.yandexMapsApiKey`.

## 3. Checkout: сохранение адреса курьера + геокод

```
SPA → PATCH /api/checkout/{id}/delivery
    → SetCheckoutDeliveryUseCase
    → PrepareCheckoutDeliveryAddress
        → если courier и нет lat/lng:
            DeliveryConfigurationRepository (city кухни)
            DeliveryAddressGeocoderPort (Yandex)
        → DeliveryAddress с координатами
    → DeliverySnapshot в агрегат Checkout
    → saveAndPresent → delivery_pricing через Promotion
```

Если геокод не удался — адрес сохраняется без координат → `in_zone: null`.

## 4. Checkout / Promotion: расчёт delivery_pricing

```
EvaluateCheckoutBenefits
    → EvaluatePromotionBenefits
        → EvaluateDeliveryBenefits
            → PromotionDeliveryPricingPort
                → PromotionDeliveryPricingAdapter
                    → DeliveryConfigurationRepository
                    → PointInGeoJsonZone (Shared/Geo)
    → ответ: delivery_pricing.in_zone, delivery_fee_kopecks, outside_zone_surcharge_*
```

Порог бесплатной доставки в зоне = `min_order_amount_kopecks` из конфига Delivery.

## 5. Оператор открывает настройки

```
Браузер → /admin/delivery
    → ManageDelivery (Filament EditRecord)
    → firstOrCreate DLV_configuration id=1
    → форма с табами «Настройки» / «Зона доставки»
```

## 6. Оператор редактирует зону на карте

```
Таб «Зона доставки»
    → iframe /admin/delivery-zone-map-editor
    → postMessage (deliveryZoneBridge)
    → deliveryZoneSyncBeforeSave перед save
    → delivery_zone_geojson, kitchen_latitude, kitchen_longitude
    → «Сохранить» → Eloquent save DLV_configuration
```

Изменения видны при следующем `GET /api/delivery` (общая БД, кэша нет).

## 7. Order (косвенно)

```
Checkout confirm
    → CheckoutConfirmed
    → Order BC копирует DeliverySnapshot из Checkout
```

Order BC к Delivery не обращается.

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | `GetDeliveryDataUseCase` → Domain port | — |
| Filament | Eloquent в форме | Eloquent save |
| Checkout geocode | `DeliveryAddressGeocoderPort` | — |
| Pricing | `PromotionDeliveryPricingAdapter` → Domain port | — |

Мутации через Application Command **не** внедрены.
