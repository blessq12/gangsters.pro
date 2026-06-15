# OrderAccountingExport — обзор BC

**Роль:** исходящая интеграция — после создания заказа в Order BC отправить полный снимок в одну или несколько **систем учёта** (Frontpad, iiko и др.).

## Семантика

| Термин | Смысл |
|--------|--------|
| **Экспорт** | Передача нашего заказа во внешнюю POS/учётную систему |
| **Система учёта** | Внешний получатель с собственным API и каталогом: `frontpad`, `iiko`, `stub` |
| **system_code** | Код системы в config и audit |
| **Product binding** | Маппинг нашего `product_id` → внешний артикул / UUID |
| **Export attempt** | Запись попытки отправки в `OAE_export_attempts` |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Слушатель `OrderCreated`, мапперы, адаптеры, HTTP-клиенты, audit | Order BC — только **публикует** `OrderCreated` |
| Config bindings товаров | Checkout BC — **не участвует** |
| | SPA / клиентский API — **не видят** экспорт |

## Принцип

**Один событие, N адаптеров.** Отличия систем — только в `AccountingSystemAdapter` (нормализация + HTTP).

Order BC не знает про Frontpad/iiko. Зависимость односторонняя: Export BC → Order Domain Event.

## Пути в коде

| Слой | OrderAccountingExport |
|------|------------------------|
| Domain | `app/Domain/OrderAccountingExport/` |
| Application | `app/Application/OrderAccountingExport/` |
| Infrastructure | `app/Infrastructure/OrderAccountingExport/` |
| Config | `config/order-accounting-export.php` |
| Provider | `App\Providers\OrderAccountingExportServiceProvider` |

HTTP-контроллеров **нет** — только исходящие вызовы.

## Связанные BC

- **[Order](../order/overview.md)** — публикует `OrderCreated` из `CreateOrderUseCase` и `CreateOrderFromIngressUseCase`
- **[AggregatorIngress](../aggregator-ingress/overview.md)** — зеркальный BC (входящий канал); общий агрегат `ORD_orders`

## Карта документации

| Файл | Содержание |
|------|------------|
| [flow.md](flow.md) | Pipeline экспорта, идемпотентность |
| [events.md](events.md) | `OrderCreated`, подписчики |
| [domain.md](domain.md) | VO, порты, исключения |
| [application.md](application.md) | Use cases, registry, ACL-мапперы |
| [infrastructure.md](infrastructure.md) | Таблицы, клиенты, адаптеры |
| [routing.md](routing.md) | Config, env, provider |
| [systems.md](systems.md) | Контракты Frontpad / iiko / stub |

## Аудит (состояние)

### Готово

- Событие `OrderCreated` в Order BC
- Pipeline: `OnOrderCreated` → `ExportOrderUseCase` → адаптеры
- Адаптеры: `stub`, `frontpad`, `iiko`
- ACL-мапперы: `FrontpadOrderMapper`, `IikoOrderMapper`
- Audit `OAE_export_attempts`
- Product bindings через config
- Unit-тесты мапперов

### Вне скоупа (следующие итерации)

| # | Тема |
|---|------|
| 1 | Таблица / UI привязок товаров (как `ING_partner_sku_bindings`) |
| 2 | Async queue + retry |
| 3 | Outbox (гарантия доставки события) |
| 4 | Геокодинг адреса для iiko |
| 5 | Polling `/api/1/commands/status` для async iiko |
| 6 | Экспорт смены статуса заказа (`OrderStatusChanged`) |
