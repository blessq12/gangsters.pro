# Catalog — Filament (оператор)

UI оператора для мутаций каталога. **Не** проходит через Application — пишет в `PRD_*` через Eloquent.

## Точка входа

- **Хаб:** `ManageCatalog` → `/admin/catalog`
- Навигация: **Каталог** (`navigationSort` = 10)
- Табы: `?tab=categories|products|sets|tags` (`activeCatalogTab`, Livewire `livewireProperty`)
- Поддерживаются legacy-значения `?tab=` (старые slug + `::tab`)

## Ресурсы

| Resource | Model | Slug | Навигация |
|----------|-------|------|-----------|
| `CatalogResource` | `PRD_Category` (form/table пустые) | `catalog` | Видим (хаб) |
| `CategoryResource` | `PRD_Category` | `catalog/categories` | Скрыта |
| `ProductResource` | `PRD_Product` (kind=product) | `catalog/products` | Скрыта |
| `ProductSetResource` | `PRD_Product` (kind=set) | `catalog/sets` | Скрыта |
| `TagResource` | `PRD_Tag` | `catalog/tags` | Скрыта |

Списки — в hub-таблицах (`*HubTable`), не на index ресурсов.

## Hub tables

| Widget | Сущность |
|--------|----------|
| `CategoriesHubTable` | Категории |
| `ProductsHubTable` | Товары |
| `ProductSetsHubTable` | Наборы |
| `TagsHubTable` | Теги |

Actions: create/edit/delete (`CatalogHubTableActions`), колонки статуса/meta (`CatalogHubTablePresentation`).

## Edit-страницы и табы

### Category (`EditCategory`)
- `category` — форма (`CategoryForm`)
- `composition` — `CategoryProductsRelationManager`

### Product (`EditProduct`)
- `card` — name, slug, description, status, price, теги, состав (`ingredients`)
- `nutrition` — КБЖУ, `nutrition_basis`
- `meta` — `meta_counts_as_roll`, `meta_gift_candidate`, `meta_is_complement_set`
- `images` — `ProductImagesRelationManager`

`catalog_kind=product` задаётся при create; `FilamentProductPersistence::ensureProductKind` на edit.

### ProductSet (`EditProductSet`)
- `card` — `ProductSetForm::cardTabSchema()`
- `composition` — `ProductSetLinesRelationManager`

Без табов tags/images/meta/nutrition.

### Tag (`EditTag`)
- Одна Section (`TagForm`)

## Relation managers

| Manager | Relationship | Reorder | Bulk |
|---------|--------------|---------|------|
| `CategoryProductsRelationManager` | `categoryProducts` | `sort_order` | нет |
| `ProductSetLinesRelationManager` | `setLines` | `sort_order` | нет |
| `ProductImagesRelationManager` | `productImages` | `sort_order` | `DeleteBulkAction` есть |

### Upload изображений (`ProductImagesRelationManager`)
- Disk: `public`
- Directory: `products/{product_id}`
- `maxSize`: 5120 KB

## Support / Concerns

| Класс | Роль |
|-------|------|
| `FilamentProductPersistence` | `normalize()`: `archived_at` по status, trim `ingredients`; `ensureProductKind` / `ensureSetKind` |
| `FilamentSlugField` | Автогенерация slug из name |
| `CatalogHubTableActions` | CRUD в hub |
| `CatalogHubTablePresentation` | Колонки таблиц |
| `CatalogContextBreadcrumbs` | Хлебные крошки с контекстом хаба |
| `RedirectsToCatalogHub` | Редирект после save → `/admin/catalog?tab=...` |
| `RendersCatalogResourceTabs` | Табы edit + relation managers |

`FilamentProductPersistence::normalize()` **не** меняет `catalog_kind`.

## Livewire registration

`AdminPanelProvider::livewireComponents()`: 4 hub tables + 3 relation managers.

## Неиспользуемый view

`resources/views/filament/catalog/widgets/catalog-overview.blade.php` — нигде не referenced.
