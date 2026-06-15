# Приём заказов от агрегаторов

Краткая точка входа. Полная документация BC — в [`.cursor/docs/aggregator-ingress/`](../.cursor/docs/aggregator-ingress/overview.md).

## Суть

Внешний маркетплейс (Яндекс Еда, Чиббис, Купер) шлёт webhook → мы нормализуем JSON → создаём заказ в `ORD_orders` с `source = aggregator`. Checkout не участвует.

## API

```
POST /api/ingress/{partner}/orders
X-Ingress-Api-Key: <секрет>
Content-Type: application/json
```

Партнёры: `stub`, `yandex-eda`, `chibbis`, `kuper`.

## Настройка

1. Env: `INGRESS_{PARTNER}_ENABLED`, `INGRESS_{PARTNER}_API_KEY` — см. `.env.example`
2. SKU: таблица `ING_partner_sku_bindings` (`partner_code`, `partner_sku` → `product_id`)
3. Config: `config/ingress.php`

## Документация по слоям

| Тема | Файл |
|------|------|
| Обзор BC | [overview.md](../.cursor/docs/aggregator-ingress/overview.md) |
| Pipeline | [flow.md](../.cursor/docs/aggregator-ingress/flow.md) |
| Контракты партнёров | [partners.md](../.cursor/docs/aggregator-ingress/partners.md) |
| HTTP / ошибки | [http.md](../.cursor/docs/aggregator-ingress/http.md) |
| Подключение нового партнёра | [application.md](../.cursor/docs/aggregator-ingress/application.md) |

## Связь с Order

Заказы с агрегаторов видны в Filament `/admin/orders` (источник «Агрегатор»). В SPA истории клиента не отображаются.

См. [Order BC](../.cursor/docs/order/overview.md).

## Тесты

```bash
php artisan test --filter IngressReceiveExternalOrder
php artisan test --filter IngressPartnerAdapters
```
