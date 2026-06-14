# Client — роутинг

## Публичное API

| Метод | Путь | Controller@action |
|-------|------|-------------------|
| `POST` | `/api/client/register` | `ClientController@register` |
| `POST` | `/api/client/login` | `ClientController@login` |
| `POST` | `/api/client/forgot-password` | `ClientController@forgotPassword` |
| `POST` | `/api/client/change-password` | `ClientController@changePassword` |
| `GET` | `/api/client/profile` | `ClientController@profile` |
| `PATCH` | `/api/client/profile` | `ClientController@updateProfile` |
| `POST` | `/api/client/addresses` | `ClientController@addAddress` |
| `DELETE` | `/api/client/addresses/{addressId}` | `ClientController@deleteAddress` |

Регистрация: `routes/api.php` → группа `api` (префикс `/api`), блок `Route::prefix('client')`.

Защищённая подгруппа: `middleware('auth:sanctum')`.

`{addressId}` — int, без доп. regex constraint.

## Filament (admin)

| Метод | Путь | Имя |
|-------|------|-----|
| `GET` | `/admin/clients` | `filament.admin.resources.clients.index` |
| `GET` | `/admin/clients/{record}` | `filament.admin.resources.clients.view` |

## Чего нет

- `POST /api/client/logout`.
- `GET /api/client/addresses` (адреса приходят в `client` объекте profile/register/login).
- Rate limit отдельно от общего `api` throttle (60/min).
