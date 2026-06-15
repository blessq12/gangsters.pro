# OrderAccountingExport — подключение

HTTP-маршрутов **нет**. BC подключается через событие и config.

## Config

`config/order-accounting-export.php`:

```php
'systems' => [
    'stub' => ['enabled' => ...],
    'frontpad' => ['enabled' => ..., 'secret' => ..., 'pay' => [...], 'product_bindings' => []],
    'iiko' => ['enabled' => ..., 'api_login' => ..., 'organization_id' => ..., ...],
],
```

## Env (`.env.example`)

### Stub (dev)

| Переменная | Смысл |
|------------|--------|
| `OAE_STUB_ENABLED` | включить заглушку |

### Frontpad

| Переменная | Смысл |
|------------|--------|
| `OAE_FRONTPAD_ENABLED` | включить экспорт |
| `OAE_FRONTPAD_SECRET` | секрет API (обязателен при enabled) |
| `OAE_FRONTPAD_ENDPOINT` | URL (default: `https://app.frontpad.ru/api/index.php?new_order`) |
| `OAE_FRONTPAD_POINT` | точка продаж (опц.) |
| `OAE_FRONTPAD_CHANNEL` | канал продаж (опц.) |
| `OAE_FRONTPAD_PAY_CASH` | код оплаты «наличные» |
| `OAE_FRONTPAD_PAY_CARD_COURIER` | код оплаты «карта курьеру» |
| `OAE_FRONTPAD_PAY_CARD_ONLINE` | код оплаты «онлайн» |

### iiko Cloud

| Переменная | Смысл |
|------------|--------|
| `OAE_IIKO_ENABLED` | включить экспорт |
| `OAE_IIKO_API_LOGIN` | API login |
| `OAE_IIKO_BASE_URL` | default: `https://api-ru.iiko.services` |
| `OAE_IIKO_ORGANIZATION_ID` | organization UUID |
| `OAE_IIKO_TERMINAL_GROUP_ID` | terminal group UUID |
| `OAE_IIKO_DEFAULT_STREET_ID` | UUID улицы-заглушки (опц.) |
| `OAE_IIKO_DEFAULT_LATITUDE` / `OAE_IIKO_DEFAULT_LONGITUDE` | координаты-заглушка (опц.) |
| `OAE_IIKO_PAYMENT_CASH_ID` | payment type UUID |
| `OAE_IIKO_PAYMENT_CARD_COURIER_ID` | |
| `OAE_IIKO_PAYMENT_CARD_ONLINE_ID` | |

По умолчанию все системы **disabled**.

## Provider

`config/app.php` → `OrderAccountingExportServiceProvider::class`.

## Очередь

Сейчас `QUEUE_CONNECTION=sync` — экспорт выполняется **синхронно** в том же запросе, что и создание заказа.

Для prod рекомендуется async listener (следующая итерация).

## Проверка в audit

```sql
SELECT * FROM OAE_export_attempts
WHERE order_id = 42
ORDER BY created_at DESC;
```

## Тесты

```bash
php artisan test --filter AccountingOrderMappers
php artisan test --filter ArchitectureBoundaries
```
