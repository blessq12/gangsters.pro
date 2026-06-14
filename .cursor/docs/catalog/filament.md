# Catalog — Filament (оператор)

UI оператора для мутаций каталога. Не заменяет Application на запись — сейчас **пишет в `PRD_*` через Eloquent** (формы, relation managers, hub-таблицы).

## Точка входа

- **Хаб:** `ManageCatalog` → `/admin/catalog`
- Табы: категории, товары, наборы, теги (`livewireProperty` — монтируется только активная таблица)

## Ресурсы

| Resource | Сущность | CRUD |
|----------|----------|------|
| `CategoryResource` | Категория | create / edit |
| `ProductResource` | Товар | create / edit |
| `ProductSetResource` | Набор (тот же `PRD_Product`, kind=set) | create / edit |
| `TagResource` | Тег | create / edit |
| `CatalogResource` | Оболочка хаба | только index |

Отдельного index-листинга у сущностей нет — списки в hub-таблицах (`*HubTable`).

## Relation managers (состав)

| Менеджер | Где | Смысл |
|----------|-----|--------|
| `CategoryProductsRelationManager` | Edit категории | Товары/наборы в категории, reorder |
| `ProductSetLinesRelationManager` | Edit набора | Строки набора, количество, reorder |
| `ProductImagesRelationManager` | Edit товара | Загрузка и порядок изображений |

Bulk-действия в составе отключены — только построчные операции.

## Паттерны UI

- Табы редактирования через `RendersCatalogResourceTabs` + именованные ключи табов.
- Хлебные крошки с контекстом хаба — `CatalogContextBreadcrumbs`.
- После save/delete — редирект обратно в хаб (`RedirectsToCatalogHub`).
- Hub-таблицы: `CatalogHubTableActions`, человекопонятные подписи вместо имён моделей.

## Регистрация Livewire

`AdminPanelProvider::livewireComponents()` — hub-таблицы и relation managers (вложенный Livewire без полной регистрации даёт 419).

## Нормализация данных

`FilamentProductPersistence` — статус/архив, `catalog_kind`, нормализация `ingredients` (JSON-массив строк) перед сохранением карточки.
