# Delivery — обзор BC

**Роль:** публичные настройки доставки — тарифы, срок, адрес кухни и полигон зоны; то, что видит клиент на витрине и что настраивает оператор в админке.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Конфигурация доставки** | Единая запись настроек BC (`DeliveryConfiguration`, singleton id=1) |
| **Настройки доставки** | Минимальная сумма заказа, стоимость доставки, стоимость за пределами зоны, среднее время доставки |
| **Зона доставки** | Адрес кухни, координаты кухни, полигон(ы) в GeoJSON |
| **Адрес кухни** | Структурированный адрес (`city`, `street`, `house`, `comment`) + строка для геокодера (`search_line`) |

## Границы

- **Внутри BC:** тарифы доставки, срок, адрес кухни, геометрия зоны.
- **Снаружи:** расчёт корзины, оформление заказа, выбор способа доставки, оплата — другие контексты (Checkout, Order, Shopping).
- Публичный контракт — **только чтение** (`GET /api/delivery`).
- Запись — **только Filament** (`/admin/delivery`).
- SPA **пока не подключена** к `GET /api/delivery` — страница доставки и док читают поля из `GET /api/system/company` через `systemStore` (контур Company / System, stub).

## Хранение

Одна таблица `DLV_configuration` — singleton-строка (id=1). Суммы в **копейках** (`*_kopecks`).

## Пути в коде

| Слой | Доставка |
|------|----------|
| Domain | `app/Domain/Delivery/` |
| Application | `app/Application/Delivery/` |
| Infrastructure | `app/Infrastructure/Delivery/` |
| HTTP | `app/Http/Controllers/Api/DeliveryController.php` |
| Filament | `app/Filament/Delivery/` |
