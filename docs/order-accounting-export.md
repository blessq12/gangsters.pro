# Экспорт заказов в системы учёта

Краткая точка входа. Полная документация BC — в [`.cursor/docs/order-accounting-export/`](../.cursor/docs/order-accounting-export/overview.md).

## Суть

После создания заказа Order BC публикует событие `OrderCreated` → BC **OrderAccountingExport** нормализует снимок и отправляет по API во внешние системы учёта (Frontpad, iiko). Checkout и SPA не участвуют.

## Поток

```
CreateOrderUseCase / CreateOrderFromIngressUseCase
  → save(order)
  → OrderCreated
  → OnOrderCreated
  → ExportOrderUseCase × N систем
  → OAE_export_attempts
```

## Системы

| Код | API | Статус |
|-----|-----|--------|
| `frontpad` | POST form `new_order` | приблизительная реализация |
| `iiko` | POST `/api/1/deliveries/create` | приблизительная реализация |
| `stub` | без HTTP (dev) | готово |

## Настройка

1. Env: `OAE_{SYSTEM}_ENABLED` и credentials — см. `.env.example`
2. Product bindings: `config/order-accounting-export.php` → `systems.{code}.product_bindings` (`product_id` → внешний код)
3. Config: `config/order-accounting-export.php`
4. Provider: `OrderAccountingExportServiceProvider` в `config/app.php`

## Документация по слоям

| Тема | Файл |
|------|------|
| Обзор BC | [overview.md](../.cursor/docs/order-accounting-export/overview.md) |
| Pipeline | [flow.md](../.cursor/docs/order-accounting-export/flow.md) |
| События | [events.md](../.cursor/docs/order-accounting-export/events.md) |
| Frontpad / iiko | [systems.md](../.cursor/docs/order-accounting-export/systems.md) |
| Config / env | [routing.md](../.cursor/docs/order-accounting-export/routing.md) |
| Подключение новой системы | [application.md](../.cursor/docs/order-accounting-export/application.md) |

## Связь с Order

Событие диспатчится только при **первом** создании (не при идемпотентном повторе). См. [Order events](../.cursor/docs/order/events.md).

Зеркальный входящий BC: [AggregatorIngress](aggregator-ingress.md).

## Тесты

```bash
php artisan test --filter AccountingOrderMappers
php artisan test --filter ArchitectureBoundaries
```
