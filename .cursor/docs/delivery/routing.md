# Delivery — роутинг

Верхнеуровневое описание точек входа в BC. Детали middleware и контроллеров — в слое HTTP.

## Публичное API

| Метод | Путь | Назначение |
|-------|------|------------|
| `GET` | `/api/delivery` | Публичные настройки доставки для SPA |

Регистрация: `routes/api.php` → группа `api` (префикс `/api`).

## SPA (витрина)

| Путь | Назначение |
|------|------------|
| `/delivery` | Страница условий доставки (`DeliveryPage`) |

Регистрация: `resources/js/router/routeRecords.js`, name `delivery`.

Данные страницы — из `GET /api/delivery`, не из отдельного маршрута.

## Админка (Filament)

| Путь | Назначение |
|------|------------|
| `/admin/delivery` | Редактирование singleton-конфигурации (табы настройки / зона) |
| `/admin/delivery-zone-map-editor` | iframe-редактор полигона зоны (Яндекс.Карты) |

Панель: `admin` (`AdminPanelProvider`), slug ресурса — `delivery`.

## Связанные маршруты (другие BC)

| Метод | Путь | Связь с Delivery |
|-------|------|------------------|
| `PATCH` | `/api/checkout/{checkoutId}/delivery` | Геокод адреса + pricing in/out zone (Checkout + Promotion) |

## Чего нет

- Нет публичных write-endpoint'ов доставки.
- Нет отдельного REST API для админских мутаций — только Filament.
- Нет маршрутов create/list/delete для конфигурации — только одна страница редактирования.
