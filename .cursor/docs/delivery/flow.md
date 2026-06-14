# Delivery — флоу

Сквозные сценарии BC без привязки к классам.

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

## 2. Витрина сегодня (фактический фронт)

```
SPA → GET /api/system/company
    → systemStore.company
    → DeliveryPage, DeliveryDockPanel, companyDeliveryFacts
```

Endpoint `/api/system/company` и `GetCompanyDataUseCase` — **stub**; фронт ожидает плоские поля (`min_order_amount_kopecks`, `delivery_zone_geojson`, …), а не контракт `{ settings, zone }` из Delivery BC.

## 3. Оператор открывает настройки

```
Браузер → /admin/delivery
    → ManageDelivery (Filament EditRecord)
    → firstOrCreate DLV_configuration id=1
    → форма с табами «Настройки» / «Зона доставки»
```

## 4. Оператор редактирует зону на карте

```
Таб «Зона доставки»
    → iframe /admin/delivery-zone-map-editor
    → postMessage (deliveryZoneBridge)
    → delivery_zone_geojson, kitchen_latitude, kitchen_longitude
    → «Сохранить» в форме → Eloquent save DLV_configuration
```

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | `GetDeliveryDataUseCase` → Domain port | — |
| Filament | Eloquent в форме | Eloquent save |

Мутации через Application Command **не** внедрены.
