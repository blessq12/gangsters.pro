# Catalog — флоу

Сквозные сценарии BC без привязки к классам.

## 1. Витрина читает меню

```
SPA → GET /api/catalog
    → CatalogController
    → GetCatalogUseCase
    → CategoryRepository + CatalogItemRepository + TagRepository
    → Eloquent PRD_* + Mappers
    → JSON { categories: [ { category, items[] } ] }
```

Только **активные** категории и позиции. Пустые категории в ответ не попадают.

## 2. Оператор открывает хаб

```
Браузер → /admin/catalog?tab=products
    → ManageCatalog (Filament Page)
    → активный HubTable (Livewire TableWidget)
    → чтение PRD_* напрямую
```

Переключение таба — смена `activeCatalogTab`, в DOM одна таблица.

## 3. Оператор редактирует товар

```
/admin/catalog/products/{id}/edit
    → табы: карточка (теги, состав JSON), nutrition, meta, изображения
    → save → Eloquent PRD_Product (+ связи тегов)
    → редирект в хаб /admin/catalog?tab=products
```

Изображения — relation manager, файл в `public` disk.

## 4. Оператор собирает категорию

```
Edit категории → таб «Состав»
    → CategoryProductsRelationManager
    → PRD_CategoryProduct (sort_order, reorder)
```

В категорию можно добавить и товар, и набор.

## 5. Оператор собирает набор

```
Edit набора → таб «Состав»
    → ProductSetLinesRelationManager
    → PRD_ProductSetLine (product_id, quantity, sort_order)
```

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | GetCatalogUseCase → Domain ports | — |
| Filament | Eloquent в таблицах/формах | Eloquent save/delete |

Целевой флоу мутаций через Application Command — пока не внедрён.
