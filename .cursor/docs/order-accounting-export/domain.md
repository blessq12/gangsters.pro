# OrderAccountingExport — слой домена

Ядро BC без Laravel/HTTP.

## Enum

| Enum | Значения |
|------|----------|
| `ExportAttemptStatus` | `success`, `failed` |

## Value Objects

| VO | Смысл |
|----|--------|
| `ExportResult` | Результат одной попытки: `status`, `externalReference?`, `errorMessage?` |

Фабрики: `ExportResult::success()`, `ExportResult::failed()`.

## Repository (ports)

| Порт | Методы |
|------|--------|
| `ExportAttemptRepository` | `hasSuccessfulExport`, `nextAttemptNumber`, `record` |
| `AccountingProductBindingRepository` | `resolveExternalProductId(systemCode, productId): ?string` |

## Exception

| Класс | Когда |
|-------|-------|
| `AccountingSystemNotConfiguredException` | нет адаптера в registry |
| `UnknownAccountingProductException` | нет binding для `product_id` |

## Зависимости

- Domain **не** импортирует Application / Infrastructure.
- Domain **не** импортирует другие Domain BC (кроме общего `Shared` при необходимости).
- Application слушает `App\Domain\Order\Event\OrderCreated` — допустимо (cross-context на уровне Application).

## Смежный BC: Order

| Элемент | Смысл |
|---------|--------|
| `OrderCreated` | точка подключения экспорта |
| `OrderLineSnapshot::productId()` | ключ для product binding |
| `OrderLineSnapshot::isPromotionBenefitLine()` | строки, исключаемые из экспорта |

См. [Order domain](../order/domain.md).
