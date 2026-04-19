# Shopping session cookie и CSRF

## Что сделано

- Сессия покупок идентифицируется **HttpOnly** cookie `gangsters_shopping_session` (имя из `config/shopping.php`, env `SHOPPING_SESSION_COOKIE`).
- Cookie выставляется middleware `EnsureShoppingSession` и уходит клиенту благодаря `AddQueuedCookiesToResponse` в группе `api` ([`app/Http/Kernel.php`](../app/Http/Kernel.php)).
- `SameSite=Lax` снижает риск CSRF от сторонних сайтов: браузер не отправит cookie на кросс-сайтовый POST.
- SPA шлёт запросы с `withCredentials: true` ([`resources/js/api/httpClient.js`](../resources/js/api/httpClient.js)), иначе cookie не цепляется.

## CSRF (Laravel VerifyCsrfToken)

- Группа `api` **не** подключает `VerifyCsrfToken`; мутации корзины идут как JSON API.
- Защита опирается на **SameSite**, **CORS** ([`CorsMiddleware`](../app/Http/Middleware/CorsMiddleware.php)) и отсутствие доверия к составу заказа из тела (`items` может быть пустым при непустой серверной корзине).

## Logout

- `POST /api/shopping/logout` удаляет серверную сессию и сбрасывает cookie (см. `LogoutShoppingSessionUseCase`), вызывается из `userStore.clearAuth()` пока ещё есть Bearer.

## Если понадобится усиление

- Подключить для `/api/shopping/*` stateful Sanctum + CSRF cookie (`/sanctum/csrf-cookie`) и заголовок `X-XSRF-TOKEN` на мутациях.
- Или требовать кастомный заголовок (`X-Requested-With` уже есть) + preflight CORS.
