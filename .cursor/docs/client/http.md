# Client — HTTP-слой

Тонкий адаптер: `FormRequest` → DTO → use case → JSON. Защищённые маршруты — `auth:sanctum`, модель токена `CLN_Client`.

## Публичный API

**Контроллер:** `App\Http\Controllers\Api\ClientController`

| Действие | HTTP | Auth | Use case |
|----------|------|------|----------|
| `register()` | `POST /api/client/register` | — | `RegisterClientUseCase` |
| `login()` | `POST /api/client/login` | — | `LoginClientUseCase` |
| `forgotPassword()` | `POST /api/client/forgot-password` | — | `RequestPasswordResetUseCase` |
| `changePassword()` | `POST /api/client/change-password` | — | `ChangePasswordWithTokenUseCase` |
| `profile()` | `GET /api/client/profile` | Sanctum | `GetClientProfileUseCase` |
| `updateProfile()` | `PATCH /api/client/profile` | Sanctum | `UpdateClientProfileUseCase` |
| `addAddress()` | `POST /api/client/addresses` | Sanctum | `AddClientAddressUseCase` |
| `deleteAddress()` | `DELETE /api/client/addresses/{addressId}` | Sanctum | `DeleteClientAddressUseCase` |

`register()` и `addAddress()` возвращают **201**; остальные — **200**.

Авторизация: `$request->user('sanctum')` должен быть `CLN_Client`, иначе `UnauthorizedException`.

## FormRequest

| Класс | Ключевые правила |
|-------|------------------|
| `RegisterClientRequest` | name, phone, email, password min:6, consent_personal_data |
| `LoginClientRequest` | phone xor email, password |
| `UpdateClientProfileRequest` | все поля optional (partial update) |
| `AddClientAddressRequest` | street, house required; make_default? |
| `ForgotPasswordRequest` | email |
| `ChangePasswordRequest` | token, password min:6 |

## Handler

`app/Exceptions/Handler.php`:

| Exception | HTTP |
|-----------|------|
| `ApiException` / `UnauthorizedException` | status из класса |
| `ClientNotFoundException` | 404 |
| `ClientAlreadyExistsException` | 422 |
| `ClientAddressNotFoundException` | 404 |
| `InvalidPasswordResetTokenException` | 422 |

На `api/client/*` при 401 dispatch `ClientUnauthorizedAccessDetected`.

## Принцип

- Контроллер не содержит бизнес-логики.
- Bearer token: `Authorization: Bearer {token}` (см. `clientAuthToken.js`).
- Админ `users` и клиент `CLN_clients` — **разные** таблицы и модели.

## SPA-клиент

`resources/js/api/clientApi.js` — зеркало endpoint'ов.

Контракты payload: `resources/js/api/clientContracts.js`.

Store: `resources/js/stores/userStore.js` (localStorage `gangsters_user`).
