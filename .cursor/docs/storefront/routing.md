# Storefront — роутинг

| Метод | Путь | Controller | Middleware |
|-------|------|------------|------------|
| `GET` | `/api/storefront/bootstrap` | `StorefrontController@bootstrap` | `api` |

Регистрация: `routes/api.php`.

Связанные маршруты оформления (Order BC, не Storefront):

| Метод | Путь |
|-------|------|
| `POST` | `/api/order-drafts/preview` |
| `POST` | `/api/orders` |
