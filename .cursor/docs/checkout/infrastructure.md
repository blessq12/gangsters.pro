# Checkout — слой инфраструктуры

Персистентность агрегата и адаптер Catalog BC.

## Модели (`CHK_*`)

| Модель | Таблица | Смысл |
|--------|---------|--------|
| `CHK_Checkout` | `CHK_checkouts` | Строка объекта оформления |

### Колонки `CHK_checkouts`

| Колонка | Смысл |
|---------|--------|
| `id` | UUID PK |
| `status` | `draft` \| `confirmed` |
| `cart_snapshot` | JSON: `{ lines: [...] }` |
| `client_snapshot` | JSON nullable |
| `delivery_snapshot` | JSON nullable |
| `payment_snapshot` | JSON nullable |
| `confirmed_at` | timestamp nullable |
| `created_at`, `updated_at` | Laravel timestamps |

Миграция: `database/migrations/2026_06_14_170000_create_chk_checkouts_table.php`.

## Mapper

`CheckoutMapper`:

- `toDomain(CHK_Checkout)` → `Checkout::restore(...)`.
- `toPersistence(Checkout)` → массив для Eloquent.

Слепки сериализуются в snake_case JSON (`product_id`, `unit_price_rubles`, …).

## Репозитории

| Класс | Порт |
|-------|------|
| `EloquentCheckoutRepository` | `CheckoutRepository` |

`save()` — `updateOrCreate` по `id`.

## Порты (адаптеры)

| Класс | Порт |
|-------|------|
| `CatalogPricingAdapter` | `CatalogPricingPort` |

Использует `CatalogItemRepository::findProductById()` + проверка `isActive()`.

## Композиция

`CheckoutServiceProvider` (`config/app.php`):

- bind `CheckoutRepository` → `EloquentCheckoutRepository`
- bind `CatalogPricingPort` → `CatalogPricingAdapter`
- listen `CheckoutConfirmed` → `OnCheckoutConfirmed`

## Сидирование

Отдельного seeder для checkout **нет** — объекты создаются через API/SPA.
