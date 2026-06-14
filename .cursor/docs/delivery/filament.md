# Delivery — Filament (оператор)

UI оператора для мутаций настроек доставки. Не заменяет Application на запись — сейчас **пишет в `DLV_configuration` через Eloquent** (форма `EditRecord`).

## Точка входа

- **Страница:** `ManageDelivery` → `/admin/delivery`
- Пункт навигации: **Доставка** (`navigationSort` = 20, иконка грузовика)
- Табы: **Настройки**, **Зона доставки**

## Ресурсы

| Resource | Сущность | Операции |
|----------|----------|----------|
| `DeliveryResource` | `DLV_Configuration` | только index (редактирование singleton) |

Отдельного листинга и create/delete **нет** — при первом заходе `firstOrCreate` создаёт строку id=1.

`DeliveryResource::form()` / `table()` пустые — схема задаётся в `ManageDelivery::form()` через `DeliveryForm`.

## Форма (`DeliveryForm`)

### Таб «Настройки»

| Поле | Колонка БД | UI |
|------|------------|-----|
| Минимальная сумма заказа | `min_order_amount_kopecks` | рубли → копейки при save |
| Стоимость доставки | `delivery_fee_kopecks` | рубли → копейки |
| Доставка за пределами зоны | `outside_zone_delivery_fee_kopecks` | рубли → копейки |
| Среднее время доставки | `average_delivery_time_minutes` | минуты |

### Таб «Зона доставки»

| Поле | Колонка БД | UI |
|------|------------|-----|
| Адрес для поиска на карте | `kitchen_address` | текст |
| Город / улица / дом / комментарий | `kitchen_city`, `kitchen_street`, `kitchen_house`, `kitchen_address_comment` | текст |
| Координаты кухни | `kitchen_latitude`, `kitchen_longitude` | hidden, заполняются с карты |
| Полигон зоны | `delivery_zone_geojson` | `YandexDeliveryZoneMap` (iframe) |

## Сохранение

- Кнопка «Сохранить» вызывает `window.deliveryZoneSyncBeforeSave($wire)` перед Livewire save — синхронизирует полигон из iframe.
- `mutateFormDataBeforeSave`: GeoJSON только `Polygon` / `MultiPolygon`, иначе `null`.
- После save — остаётся на той же странице (`getRedirectUrl` = `null`).
- Уведомление: «Настройки доставки сохранены».

## Карта зоны

| Компонент | Путь | Смысл |
|-----------|------|--------|
| `YandexDeliveryZoneMap` | `app/Filament/Delivery/Forms/Components/` | Filament Field, view `filament.forms.components.yandex-delivery-zone-map` |
| iframe-редактор | `resources/views/admin/delivery-zone-map-editor.blade.php` | Яндекс.Карты, рисование полигона, геокод кухни |
| postMessage-мост | `public/js/filament/delivery-zone-iframe-bridge.js` | Alpine `deliveryZoneBridge`, `deliveryZoneSyncBeforeSave` |

Маршрут iframe: `/admin/delivery-zone-map-editor` (`filament.admin.delivery-zone-map-editor`).

Ключи карт: `config/services.php` → `yandex_maps.api_key`, `yandex_maps.geocoder_api_key`.

Скрипт моста подключается в `AdminPanelProvider::assets()`.

## Паттерны UI

- `ManageDelivery` extends `EditRecord`, зарегистрирован как `index`-страница ресурса (требование Filament для пункта навигации).
- `mount()` использует `DeliveryConfigurationRepository::SINGLETON_ID` для `firstOrCreate`.

## Регистрация

`AdminPanelProvider::resources()` — `DeliveryResource::class`.
