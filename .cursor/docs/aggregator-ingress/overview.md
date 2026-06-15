# AggregatorIngress — обзор BC

**Роль:** приём заказов от внешних маркетплейсов доставки (агрегаторов) через HTTP webhook. Нормализация чужого JSON → создание **нашего** заказа в Order BC.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Агрегатор** | Внешний партнёр (Яндекс Еда, Чиббис, Купер) со своим каталогом SKU и заказом |
| **Ingress** | Входящий канал: `POST /api/ingress/{partner}/orders` |
| **Partner code** | Код партнёра в URL и БД: `yandex-eda`, `chibbis`, `kuper`, `stub` |
| **external_order_id** | ID заказа в системе партнёра; ключ идемпотентности |
| **IngressMappedOrder** | Нормализованный снимок после ACL-адаптера (до резолва SKU) |
| **SKU binding** | Маппинг `partner_sku` → наш `product_id` в `ING_partner_sku_bindings` |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Auth, ACL-адаптеры, pipeline, audit | Order BC — единственный write в `ORD_orders` |
| Catalog binding (read) | Checkout BC — **не участвует** |
| Stub + шаблоны партнёров | SPA / клиентский API — **не видят** ingress-заказы |

## Принцип

**Один pipeline, N адаптеров.** Отличия партнёров — только в `IngressPartnerAdapter` (парсинг JSON + `extractExternalOrderId`).

## Пути в коде

| Слой | AggregatorIngress |
|------|-------------------|
| Domain | `app/Domain/AggregatorIngress/` |
| Application | `app/Application/AggregatorIngress/` |
| Infrastructure | `app/Infrastructure/AggregatorIngress/` |
| HTTP | `app/Http/Controllers/Api/IngressController.php` |
| Config | `config/ingress.php` |
| Provider | `App\Providers\AggregatorIngressServiceProvider` |

## Связанные BC

- **[Order](../order/overview.md)** — агрегат исполнения; `CreateOrderFromIngressUseCase`, `OrderSource::Aggregator`
- **[Catalog](../catalog/overview.md)** — резолв `product_id` по SKU binding

## Карта документации

| Файл | Содержание |
|------|------------|
| [flow.md](flow.md) | Pipeline приёма, идемпотентность |
| [domain.md](domain.md) | VO, порты, исключения |
| [application.md](application.md) | Use cases, registry, ACL |
| [infrastructure.md](infrastructure.md) | Таблицы, адаптеры, audit |
| [http.md](http.md) | API, auth, коды ошибок |
| [routing.md](routing.md) | Маршруты, env, provider |
| [partners.md](partners.md) | Контракты stub / Яндекс Еда / Чиббис / Купер |

## Аудит (состояние)

### Готово

- Pipeline `ReceiveExternalOrderUseCase`
- `CreateOrderFromIngressUseCase` в Order BC
- Адаптеры: `stub`, `yandex-eda`, `chibbis`, `kuper`
- SKU bindings + ingress audit
- Feature-тест stub; unit-тесты мапперов
- Filament: колонка «Источник», подписи партнёров

### Вне скоупа

| # | Тема |
|---|------|
| 1 | Callback статусов агрегатору |
| 2 | Синхронизация меню / стоп-листы |
| 3 | UI управления SKU bindings в Filament |
| 4 | ~~Исходящие доменные события (`OrderCreated`)~~ — реализовано в Order BC |
