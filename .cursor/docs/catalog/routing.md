# Catalog — роутинг

Верхнеуровневое описание точек входа. Детали контроллера — в `http.md`.

## Публичное API

| Метод | Путь | Назначение |
|-------|------|------------|
| `GET` | `/api/catalog` | Дерево каталога для витрины SPA |

Регистрация: `routes/api.php`, группа `api` (префикс `/api`).

## SPA (витрина)

Отдельного маршрута страницы каталога **нет** — меню на **главной** (`HomePageDesktop` / `HomePageMobile`).

Данные — из `GET /api/catalog`.

## Админка (Filament)

| Путь | Назначение |
|------|------------|
| `/admin/catalog` | Хаб: `?tab=categories\|products\|sets\|tags` |
| `/admin/catalog/categories/create` | Создание категории |
| `/admin/catalog/categories/{id}/edit` | Редактирование + состав |
| `/admin/catalog/products/create` | Создание товара |
| `/admin/catalog/products/{id}/edit` | Карточка, nutrition, meta, изображения |
| `/admin/catalog/sets/create` | Создание набора |
| `/admin/catalog/sets/{id}/edit` | Карточка + состав набора |
| `/admin/catalog/tags/create` | Создание тега |
| `/admin/catalog/tags/{id}/edit` | Редактирование тега |

Панель: `admin` (`AdminPanelProvider`), slug хаба — `catalog`.

## Чего нет

- Нет публичных write-endpoint'ов каталога.
- Нет REST API для админских мутаций — только Filament.
- Нет отдельного API для изображений/meta — только полный `GET /api/catalog`.
