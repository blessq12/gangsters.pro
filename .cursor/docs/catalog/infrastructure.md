# Catalog — инфраструктура

Реализация доменных портов и персистентность. Единственное место, где живут Eloquent и таблицы `PRD_*`.

## Таблицы

Миграции: `database/migrations/2026_06_14_100000_create_prd_catalog_tables.php`, `2026_06_16_120000_add_sku_to_prd_products.php`.

| Таблица | Назначение |
|---------|------------|
| `PRD_categories` | Категории: `name`, `slug`, `sort_order`, `is_active` |
| `PRD_products` | Товары и наборы: `catalog_kind`, `status`, `sku` (nullable, unique, только смысл для `product`), `price` (рубли), КБЖУ, `ingredients` (json), meta-поля, `archived_at` |
| `PRD_category_product` | Состав категории: `category_id`, `product_id`, `sort_order` |
| `PRD_tags` | Теги: `code`, `label`, `color`, `is_active`, `sort_order` |
| `PRD_product_tag` | Pivot товар/набор ↔ тег |
| `PRD_product_set_lines` | Строки набора: `set_id`, `product_id`, `quantity`, `sort_order` |
| `PRD_product_images` | Изображения товара: `disk`, `path`, `sort_order` |

## Eloquent-модели

| Модель | Таблица |
|--------|---------|
| `PRD_Category` | `PRD_categories` |
| `PRD_Product` | `PRD_products` |
| `PRD_Tag` | `PRD_tags` |
| `PRD_CategoryProduct` | `PRD_category_product` |
| `PRD_ProductSetLine` | `PRD_product_set_lines` |
| `PRD_ProductImage` | `PRD_product_images` |

Eloquent-модели **`PRD_ProductTag` нет** — pivot `PRD_product_tag` читается через `DB::table` в `EloquentCatalogItemRepository`.

### `PRD_Product` relations
- `categories()`, `tags()`, `setLines()`, `productImages()`

## Mappers

| Класс | Маппинг |
|-------|---------|
| `CatalogCategoryMapper` | `PRD_Category` → `Category` |
| `CatalogProductMapper` | `PRD_Product` → `Product`; trim `sku`, пусто → `null`; `archived_at !== null` → `Archived`; nutrition `null` если все КБЖУ = 0 |
| `CatalogProductSetMapper` | `PRD_Product` + lines → `ProductSet`; пустые lines → `null` |
| `CatalogTagMapper` | `PRD_Tag` → `Tag` |

## Repositories

| Класс | Порт |
|-------|------|
| `EloquentCategoryRepository` | `CategoryRepository` |
| `EloquentCatalogItemRepository` | `CatalogItemRepository` |
| `EloquentTagRepository` | `TagRepository` |

`findItemsByCategoryId` не фильтрует по статусу позиций — фильтрация в Application use case.

Tag ids для product/set — `DB::table('PRD_product_tag')`, порядок ids сохраняется.

## Композиция

`CatalogServiceProvider` (`config/app.php`):

```php
CategoryRepository → EloquentCategoryRepository
CatalogItemRepository → EloquentCatalogItemRepository
TagRepository → EloquentTagRepository
```

Отдельного `config/catalog.php` **нет**.

## Сидирование

`Database\Seeders\CatalogSeeder` — 4 тега, 4 категории, 4 товара, 1 набор, связи, product tags. Вызывается из `DatabaseSeeder`.

## Тесты

| Файл | Покрытие |
|------|----------|
| `tests/Feature/CatalogReadTest.php` | `GET /api/catalog` → 200, структура `categories` |

Unit-тестов mappers/repos/Filament **нет**.
