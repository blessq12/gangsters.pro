# Catalog — слой инфраструктуры

Реализация доменных портов и персистентность. Единственное место, где живут Eloquent и таблицы `PRD_*`.

## Модели (`PRD_*`)

| Модель | Таблица | Смысл |
|--------|---------|--------|
| `PRD_Category` | `PRD_categories` | Категории |
| `PRD_Product` | `PRD_products` | Товары и наборы (`catalog_kind`) |
| `PRD_Tag` | `PRD_tags` | Теги |
| `PRD_CategoryProduct` | `PRD_category_product` | Состав категории |
| `PRD_ProductTag` | `PRD_product_tag` | Теги на позиции |
| `PRD_ProductSetLine` | `PRD_product_set_lines` | Строки набора |
| `PRD_ProductImage` | `PRD_product_images` | Изображения товара |

## Репозитории

| Класс | Порт |
|-------|------|
| `EloquentCategoryRepository` | `CategoryRepository` |
| `EloquentCatalogItemRepository` | `CatalogItemRepository` |
| `EloquentTagRepository` | `TagRepository` |

Маппинг строк БД ↔ домен — через `Mapper/*` в том же слое.

## Композиция

`CatalogServiceProvider` регистрирует привязки портов к Eloquent-реализациям.

## Guards / Storage

Заготовки под проверки удаления (категория/товар/тег занят) и локальное хранение изображений — инфраструктурные адаптеры для будущих application-сценариев. Filament сейчас пишет в модели напрямую, минуя эти порты.

## Сидирование

`Database\Seeders\CatalogSeeder` — демо-данные категорий, товаров, набора, тегов и связей для локальной разработки.
