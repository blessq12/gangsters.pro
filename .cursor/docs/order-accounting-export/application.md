# OrderAccountingExport — Application

## Handler (Observer)

| Класс | Назначение |
|-------|------------|
| `OnOrderCreated` | Fan-out: для каждого enabled адаптера с `supports()` → `ExportOrderUseCase` |

## Use case

| Класс | Назначение |
|-------|------------|
| `ExportOrderUseCase` | Один заказ → одна система учёта: идемпотентность, маппинг через адаптер, audit |

## Порты (Strategy / Adapter)

| Интерфейс | Методы |
|-----------|--------|
| `AccountingSystemAdapter` | `systemCode()`, `isEnabled()`, `supports(OrderCreated)`, `export(OrderCreated)` |

## Сервисы

| Класс | Роль |
|-------|------|
| `AccountingAdapterRegistry` | `resolve(systemCode)`, `enabled()` |

## ACL-мапперы

| Класс | Направление |
|-------|-------------|
| `OrderCreatedToExportPayloadMapper` | Универсальный снимок (отладка / общий DTO) |
| `FrontpadOrderMapper` | `OrderCreated` → form-параметры Frontpad `new_order` |
| `IikoOrderMapper` | `OrderCreated` → JSON `/api/1/deliveries/create` |

Мапперы используют `AccountingProductBindingRepository` для резолва товаров.

## Pipeline `ExportOrderUseCase`

1. `hasSuccessfulExport` → early return
2. `adapterRegistry.resolve(systemCode)`
3. `adapter.supports` (дубль-проверка при прямом вызове)
4. `adapter.export` → внутри: mapper + HTTP client
5. `exportAttempts.record`

## Подключение новой системы учёта

1. Класс `*AccountingSystemAdapter` в `Infrastructure/OrderAccountingExport/Adapter/`
2. ACL-маппер в `Application/OrderAccountingExport/Mapper/`
3. HTTP-клиент в `Infrastructure/OrderAccountingExport/Client/` (при необходимости)
4. Секция в `config/order-accounting-export.php`
5. Регистрация в `OrderAccountingExportServiceProvider`
6. `product_bindings` для системы
7. Контракт в [systems.md](systems.md)
8. Unit-тест маппера в `tests/Unit/OrderAccountingExport/`

## Зависимость от Order BC

| Класс | BC | Роль |
|-------|-----|------|
| `OrderCreated` | Order Domain | входной payload |
| — | Order | **нет write** — только чтение события |
