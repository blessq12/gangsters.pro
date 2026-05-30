# Админка: аналитика (дашборд)

## URL

- Страница: `/admin/dashboard`
- Query: `period` (`today` | `7d` | `30d` | `mtd`), `tab` (`overview` | `finance` | `clients` | `orders` | `storefront`)

Пример: `/admin/dashboard?period=30d&tab=finance`

## Табы (семантические хабы)

| Tab | Содержимое |
|-----|------------|
| `overview` | KPI выручки, pipeline заказов |
| `finance` | Финансы, тренды, mix доставки/оплаты |
| `clients` | KPI клиентов, топ клиентов |
| `orders` | Pipeline, каналы, последние заказы (ссылка в Операции) |
| `storefront` | Shopping funnel, топ товаров |

## Application-слой

- Контракт: `App\Application\Reporting\Query\BusinessMetricsReader`
- Секции: `MetricsSection` + DTO (`OverviewMetricsDto`, `FinanceMetricsDto`, …)
- Реализация: `EloquentBusinessMetricsReader`, кэш **120 с**, ключ включает scope, period и час
- DI: `ReportingServiceProvider`

## Filament

- Страница: `App\Filament\Analytics\Pages\ManageAnalytics`
- Hub-панели на таб: `Hub*Panel` → нативные `StatsOverviewWidget`, `ChartWidget`, `TableWidget`

## Ограничения данных

- Агрегаты заказов — таблица `ORD_orders` (суммы в копейках).
- Топ клиентов — `reporting_client_order_facts` + `UR_clients` (если таблиц нет — пустой список).
- Funnel — `SHP_shopping_sessions` и связанные таблицы Shopping.
