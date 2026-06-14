# Catalog — роутинг

Верхнеуровневое описание точек входа в BC. Детали middleware и контроллеров — в слое HTTP.

## Публичное API

| Метод | Путь | Назначение |
|-------|------|------------|
| `GET` | `/api/catalog` | Дерево каталога для витрины SPA |

Регистрация: `routes/api.php` → группа `api` (префикс `/api`).

## Админка (Filament)

| Путь | Назначение |
|------|------------|
| `/admin/catalog` | Хаб: табы категории / товары / наборы / теги |
| `/admin/catalog/categories/create` | Создание категории |
| `/admin/catalog/categories/{id}/edit` | Редактирование категории и состава |
| `/admin/catalog/products/create` | Создание товара |
| `/admin/catalog/products/{id}/edit` | Карточка, мета, изображения |
| `/admin/catalog/products/{id}/edit` (набор) | Аналогичные маршруты для наборов через `ProductSetResource` |
| `/admin/catalog/tags/create` | Создание тега |
| `/admin/catalog/tags/{id}/edit` | Редактирование тега |

Панель: `admin` (`AdminPanelProvider`), slug ресурсов — `catalog/*`.

## Чего нет

- Нет публичных write-endpoint'ов каталога.
- Нет отдельного REST API для админских мутаций — только Filament.
