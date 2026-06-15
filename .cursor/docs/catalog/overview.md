# Catalog — обзор BC

**Тип:** Core Domain  
**Роль:** справочник витрины — что видит клиент в меню и что настраивает оператор в админке.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Категория** | Раздел меню с упорядоченным списком позиций |
| **Товар** | Продаваемая позиция с ценой, **SKU**, описанием, пищевой ценностью, составом, тегами |
| **Набор** | Позиция каталога, собранная из нескольких товаров (состав набора и количества) |
| **Тег** | Справочная метка для витрины (новинка, хит и т.п.) |
| **Состав категории** | Связь категория ↔ товар/набор с порядком сортировки (`PRD_category_product`) |
| **Позиция каталога** | Обобщение: товар и набор равноправны в категории (`CatalogItem`) |
| **Meta-поля товара** | `meta_counts_as_roll`, `meta_gift_candidate`, `meta_is_complement_set` — Filament + Order ACL + `promotion_meta` в bootstrap catalog |
| **SKU** | Уникальный артикул (`PRD_products.sku`); в домене у `Product`; в админке — на карточке |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Структура меню, карточки товаров/наборов, теги, медиа товара, порядок в категории | Корзина, OrderDraft, оплата, доставка, клиенты |
| Публичный read API `GET /api/catalog` и bootstrap catalog | Order читает цены и meta через ACL-порты |
| Запись — Filament (`/admin/catalog`) | Promotion BC не импортирует Catalog напрямую |

Публичный контракт — **только чтение** (`GET /api/catalog`).  
Запись — **только Filament** (`/admin/catalog`).  
SPA: каталог на **главной** (`HomePage*`), не отдельный route.

## Хранение

Единая группа таблиц `PRD_*`: категории, товары/наборы в `PRD_products` (`catalog_kind`), теги, pivot `PRD_product_tag`, связи категории, строки набора, изображения.

Миграция: `database/migrations/2026_06_14_100000_create_prd_catalog_tables.php`, `2026_06_16_120000_add_sku_to_prd_products.php`.  
Сид: `Database\Seeders\CatalogSeeder`.

Цена в БД — **рубли** (`PRD_products.price`, `unsignedBigInteger`).

## Пути в коде

| Слой | Каталог |
|------|---------|
| Domain | `app/Domain/Catalog/` |
| Application | `app/Application/Catalog/` — один read use case |
| Infrastructure | `app/Infrastructure/Catalog/` |
| HTTP | `app/Http/Controllers/Api/CatalogController.php` |
| Filament | `app/Filament/Catalog/` |
| Composition | `app/Providers/CatalogServiceProvider.php` |
| SPA | `resources/js/stores/catalogStore.js`, `resources/js/features/catalog/`, `resources/js/domain/catalog/`, `resources/js/components/catalog/` |
| Order ACL | `app/Infrastructure/Order/Port/Catalog*Adapter.php` |

## Аудит (состояние на 2026-06-14)

### Готово

- Полная вертикаль read: Domain → Application → Infrastructure → HTTP.
- Filament hub `/admin/catalog` с 4 табами, CRUD через скрытые ресурсы, 3 relation managers.
- SPA: `catalogStore`, `useCatalogReadModel`, `useCatalogPageModel`, карточки/модалки на главной, localStorage-кэш.
- Order ACL: 4 порта (`CatalogPricingPort`, …) — биндинг `OrderServiceProvider`.
- Bootstrap catalog item включает `promotion_meta` для локального wizard на SPA.
- Feature-тест `GET /api/catalog` → 200.

### Пробелы / техдолг

- Write в админке **мимо** Application (Filament → Eloquent).
- **`GET /api/catalog` не отдаёт `images`** — frontend (`catalogMappers.js`) ожидает `images[].variants[]`, картинки на витрине пустые.
- Meta-поля и изображения **не** в domain entities и публичном API.
- Eloquent-модели `PRD_ProductTag` **нет** (pivot через `DB::table`).
- Нет unit-тестов mappers/repos/Filament; нет `config/catalog.php`.
- `resources/views/filament/catalog/widgets/catalog-overview.blade.php` — не подключена.
