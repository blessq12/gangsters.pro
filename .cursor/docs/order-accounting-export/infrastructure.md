# OrderAccountingExport — Infrastructure

## Таблица `OAE_export_attempts`

| Колонка | Смысл |
|---------|--------|
| `order_id` | FK на `ORD_orders.id` |
| `system_code` | `frontpad`, `iiko`, `stub`, … |
| `status` | `success` \| `failed` |
| `attempt` | номер попытки (1, 2, …) |
| `external_reference` | id заказа во внешней системе |
| `error_message` | текст ошибки при `failed` |
| `created_at` | |

Индексы: `(order_id, system_code)`, `status`.

Миграция: `database/migrations/2026_06_16_100000_create_oae_export_attempts_table.php`.

## Модель

| Класс | Таблица |
|-------|---------|
| `OAE_ExportAttempt` | `OAE_export_attempts` |

## Repository

| Класс | Port |
|-------|------|
| `EloquentExportAttemptRepository` | `ExportAttemptRepository` |
| `ConfigAccountingProductBindingRepository` | `AccountingProductBindingRepository` — читает `config('order-accounting-export.systems.{code}.product_bindings')` |

### Product bindings (операционно)

Пока через config / env. Пример в `config/order-accounting-export.php`:

```php
'frontpad' => [
    'product_bindings' => [
        '10' => '001',  // product_id => артикул Frontpad
    ],
],
'iiko' => [
    'product_bindings' => [
        '10' => '550e8400-e29b-41d4-a716-446655440000',  // product_id => UUID iiko
    ],
],
```

UI в Filament — техдолг.

## HTTP-клиенты

| Класс | API |
|-------|-----|
| `FrontpadApiClient` | POST form → `new_order` |
| `IikoApiClient` | POST JSON → `/api/1/access_token`, `/api/1/deliveries/create` |

`IikoApiClient` — singleton, кэширует `access_token` в рамках процесса.

## Адаптеры систем учёта

| Класс | `systemCode()` |
|-------|----------------|
| `StubAccountingSystemAdapter` | `stub` |
| `FrontpadAccountingSystemAdapter` | `frontpad` |
| `IikoAccountingSystemAdapter` | `iiko` |

## Provider

`OrderAccountingExportServiceProvider`:

- bind `ExportAttemptRepository`, `AccountingProductBindingRepository`
- singleton `IikoApiClient`, `AccountingAdapterRegistry`
- `Event::listen(OrderCreated, OnOrderCreated)`

См. [routing.md](routing.md).
