# AggregatorIngress — Infrastructure

## Таблицы

### `ING_partner_sku_bindings`

| Колонка | Смысл |
|---------|--------|
| `partner_code` | код партнёра |
| `partner_sku` | SKU в каталоге партнёра |
| `product_id` | FK на `PRD_products` |

Unique: `(partner_code, partner_sku)`.

### `ING_ingress_audits`

| Колонка | Смысл |
|---------|--------|
| `partner_code` | |
| `external_order_id` | |
| `order_id` | nullable — наш PK при успехе |
| `result` | `accepted` \| `idempotent` \| `rejected` |
| `raw_payload` | json — сырой webhook |
| `created_at` | |

Миграция: `database/migrations/2026_06_15_100100_create_ing_ingress_tables.php`.

## Расширение `ORD_orders`

Миграция: `2026_06_15_100000_extend_ord_orders_for_aggregator_ingress.php`.

| Колонка | Смысл |
|---------|--------|
| `source` | `site` \| `aggregator` |
| `partner_code` | nullable |
| `external_order_id` | nullable |
| `checkout_id` | nullable для aggregator |

Unique: `(partner_code, external_order_id)`.

## Модели

| Класс | Таблица |
|-------|---------|
| `ING_PartnerSkuBinding` | `ING_partner_sku_bindings` |
| `ING_IngressAudit` | `ING_ingress_audits` |

## Repository

| Класс | Port |
|-------|------|
| `EloquentPartnerCatalogBindingRepository` | `PartnerCatalogBindingRepository` — join с `PRD_Product` для имени |
| `EloquentIngressAuditRepository` | `IngressAuditRepository` |

## Адаптеры партнёров

| Класс | `partnerCode()` |
|-------|-----------------|
| `StubIngressPartnerAdapter` | `stub` |
| `YandexEdaIngressPartnerAdapter` | `yandex-eda` |
| `ChibbisIngressPartnerAdapter` | `chibbis` |
| `KuperIngressPartnerAdapter` | `kuper` |

Support: `IngressAdapterSupport` — parseDateTime, requireString, rublesFromKopecks.

## Auth

`ConfigIngressPartnerAuthenticator` — сверка `X-Ingress-Api-Key` с `config('ingress.partners.{code}.api_key')`.

## Provider

`AggregatorIngressServiceProvider`:

- bind repositories + authenticator
- singleton `IngressPartnerAdapterRegistry` со всеми адаптерами
