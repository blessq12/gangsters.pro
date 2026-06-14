# Catalog — слой домена

Ядро BC: сущности, value objects, перечисления и контракты доступа к данным. Без Laravel, без HTTP, без Filament.

## Сущности и контракты

### `Category`
- `id`, `name`, `slug`, `sortOrder`, `isActive`
- Методы: геттеры по каждому полю

### `Product` (implements `CatalogItem`)
- `id`, `name`, `slug`, `status: ProductStatus`, `price: Money`, `description`, `nutrition`, `tagIds: list<int>`, `ingredients: list<string>`
- `kind()` → `CatalogItemKind::Product`
- Методы: `id()`, `kind()`, `name()`, `slug()`, `status()`, `isActive()`, `price()`, `description()`, `nutrition()`, `tagIds()`, `ingredients()`

### `ProductSet` (implements `CatalogItem`)
- `id`, `name`, `slug`, `status`, `price: Money`, `description`, `lines: list<ProductSetLine>`, `tagIds: list<int>`
- Инвариант: пустой `lines` → `InvalidArgumentException` при создании
- `kind()` → `CatalogItemKind::Set`

### `Tag`
- `id`, `code`, `label`, `color`, `isActive`, `sortOrder`

### `CategoryItem`
- `categoryId`, `catalogItemId`, `kind: CatalogItemKind`, `sortOrder`

### `CatalogItem` (interface)
- `id()`, `kind()`, `name()`, `slug()`, `status()`, `isActive()` — **без** price/description

## Value objects

| VO | Поля |
|----|------|
| `Nutrition` | `calories`, `proteins`, `fats`, `carbs`, `basis` (default `'per_100g'`) |
| `ProductSetLine` | `productId` (≥1), `quantity` (≥1) |

## Перечисления

| Enum | Значения |
|------|----------|
| `CatalogItemKind` | `product`, `set` |
| `ProductStatus` | `active`, `archived`; метод `isActive(): bool` |

## Репозитории (порты)

### `CategoryRepository`
| Метод | Смысл |
|-------|--------|
| `findAllOrdered()` | Активные категории, `sort_order`, `id` |
| `findById(int)` | Категория по id |
| `findItemsByCategoryId(int)` | Состав категории (все links, kind из `catalog_kind`) |

### `CatalogItemRepository`
| Метод | Смысл |
|-------|--------|
| `findProductById(int)` | Товар по id |
| `findSetById(int)` | Набор по id |
| `findActiveProductsByIds(list<int>)` | Активные товары: `catalog_kind=product`, `status=active`, `archived_at IS NULL` |
| `findActiveSetsByIds(list<int>)` | Активные наборы: `catalog_kind=set`, active, не archived, с lines |

### `TagRepository`
| Метод | Смысл |
|-------|--------|
| `findAllActiveOrdered()` | Активные теги по `sort_order` |
| `findById(int)` | Тег по id |
| `findByIds(list<int>)` | Теги по ids |
| `findTagIdsByProductId(int)` | Tag ids товара |
| `findTagIdsBySetId(int)` | Tag ids набора |

Домен **не знает** про таблицы `PRD_*`, Eloquent, изображения, meta-поля checkout.

## Инварианты (семантика)

- В публичный API попадают только **активные** категории и позиции; архивные пропускаются.
- Категории без активных items **не** попадают в ответ API.
- Товар и набор — разные виды одной таблицы хранения (`catalog_kind`), в домене — разные сущности.
- Цена — `App\Shared\ValueObject\Money` (рубли).

## Чего нет в домене

- Изображений, meta-полей (`meta_gift_candidate` и т.д.).
- Событий, write-команд, портов Checkout.
