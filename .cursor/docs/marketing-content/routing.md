# MarketingContent — роутинг

Верхнеуровневое описание точек входа. Детали контроллера — в `http.md`.

## Публичное API

| Метод | Путь | Назначение |
|-------|------|------------|
| `GET` | `/api/marketing` | Баннеры + акции (`{ data: { banners, promotions } }`) |
| `GET` | `/api/marketing/banners` | Только баннеры |
| `GET` | `/api/marketing/promotions` | Только акции |

Регистрация: `routes/api.php`, группа `api` (префикс `/api`).

SPA использует `/banners` и `/promotions`, не combined `/marketing`.

## SPA (витрина)

Отдельного маршрута страницы для marketing BC нет — контент на **главной** (`HomePage*`).

Данные — из API выше, не из отдельного page route.

## Админка (Filament)

| Путь | Назначение |
|------|------------|
| `/admin/marketing` | Хаб (табы баннеры / акции, `?tab=`) |
| `/admin/marketing/banners/create` | Новый баннер |
| `/admin/marketing/banners/{id}/edit` | Редактирование баннера |
| `/admin/marketing/promotions/create` | Новая акция |
| `/admin/marketing/promotions/{id}/edit` | Редактирование акции |

Панель: `admin` (`AdminPanelProvider`), slug хаба — `marketing`.

## Чего нет

- Нет публичных write-endpoint'ов.
- Нет REST API для админских мутаций — только Filament.
- Нет отдельного list/create/delete маршрута у хаба — CRUD через скрытые ресурсы.
