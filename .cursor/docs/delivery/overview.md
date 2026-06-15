# Delivery — обзор BC

**Роль:** публичные настройки доставки — тарифы, срок, адрес кухни и полигон зоны; то, что видит клиент на витрине и что настраивает оператор в админке.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Конфигурация доставки** | Единая запись настроек BC (`DeliveryConfiguration`, singleton id=1) |
| **Настройки доставки** | Минимальная сумма заказа, стоимость доставки в зоне, стоимость за пределами зоны, среднее время доставки |
| **Зона доставки** | Адрес кухни, координаты кухни, полигон(ы) в GeoJSON |
| **Адрес кухни** | Структурированный адрес (`city`, `street`, `house`, `comment`) для текстов на витрине |
| **Зона доставки (покрытие)** | GeoJSON-полигон + координаты кухни; in/out zone при OrderDraft preview — не строка адреса |
| **Тариф in/out zone** | Расчёт стоимости курьерской доставки по сумме корзины и попаданию адреса в GeoJSON-полигон — **не в Delivery BC**, а в Promotion через `PromotionDeliveryPricingPort` |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Тарифы доставки, срок, адрес кухни, геометрия зоны | OrderDraft (выбор способа, geocode адреса) |
| Порт геокодирования адреса курьера (`DeliveryAddressGeocoderPort`) | Расчёт `delivery_pricing` / benefits (Promotion BC) |
| Публичный read API `GET /api/delivery` и bootstrap `delivery` | Слепок доставки в Order (из OrderDraft при place) |
| Запись настроек — Filament (`/admin/delivery`) | `DeliveryMethod` (`courier` \| `pickup`) — `app/Shared/Enum/DeliveryMethod.php` |

Публичный контракт BC — **только чтение** (`GET /api/delivery`).  
Запись — **только Filament** (`/admin/delivery`).

## Интеграции с другими BC

| BC | Направление | Как |
|----|-------------|-----|
| **Order (OrderDraft)** | Delivery → Order | `PrepareOrderDraftDeliveryAddress` — geocode через `DeliveryAddressGeocoderPort` |
| **Promotion** | Delivery → Promotion | `PromotionDeliveryPricingAdapter` — базовые тарифы + зона; порог free delivery из PRM |
| **Order** | косвенно | `DeliverySnapshot` в слепке заказа при place; к Delivery BC не обращается |

## Хранение

Одна таблица `DLV_configuration` — singleton-строка (id=1). Суммы в **копейках** (`*_kopecks`).

Миграция: `database/migrations/2026_06_14_140000_create_dlv_configuration_table.php`.  
Сид: `Database\Seeders\DeliverySeeder` (Томск, демо-тарифы, полигон).

## Пути в коде

| Слой | Доставка |
|------|----------|
| Domain | `app/Domain/Delivery/` |
| Application | `app/Application/Delivery/` — один read use case |
| Infrastructure | `app/Infrastructure/Delivery/` |
| HTTP | `app/Http/Controllers/Api/DeliveryController.php` |
| Filament | `app/Filament/Delivery/` |
| Composition | `app/Providers/DeliveryServiceProvider.php` |
| SPA | `resources/js/stores/deliveryStore.js`, `resources/js/features/delivery/`, `resources/js/domain/delivery/` |

## Аудит (состояние на 2026-06-14)

### Готово

- Полная вертикаль read: Domain → Application → Infrastructure → HTTP (`GET /api/delivery`).
- Singleton-конфиг с тарифами in/out zone, GeoJSON зоной, структурированным адресом кухни.
- Filament: табы «Настройки» / «Зона доставки», iframe-редактор полигона (Яндекс.Карты), postMessage-мост.
- Порт геокодирования + Yandex adapter; используется в OrderDraft pipeline.
- SPA: bootstrap `delivery` + legacy `GET /api/delivery`.
- Feature: `PlaceOrderTest` (courier in/out zone через place flow).

### Пробелы / техдолг

- Write в админке **мимо** Application Delivery (Filament → Eloquent напрямую).
- Нет доменных событий при изменении конфига.
- Нет тестов: `GetDeliveryDataUseCase`, `DeliveryController`, geocoder, repository, mapper, `GET /api/delivery`.
- `PromotionDeliveryPricingAdapter::resolveDeliveryFeeKopecks` игнорирует `$promotionPolicy` (акционные надбавки — в Promotion domain, не здесь).
- Геокодер при отсутствии API key / ошибке HTTP возвращает `null` → `in_zone: null`, зональная логика не применяется.
- Legacy-имена на фронте: `companyDeliveryFacts.js`, `companyDeliveryZoneMap.js` (по смыслу — delivery facts).
