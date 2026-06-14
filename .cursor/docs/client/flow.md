# Client — флоу

Сквозные сценарии BC.

## 1. Регистрация (SPA)

```
ClientRegisterForm
  → userStore.registerClient()
  → buildRegisterClientPayload()
  → POST /api/client/register
  → RegisterClientUseCase
  → save CLN_client
  → Sanctum token
  → setProfile + setToken + localStorage
  → emit CLIENT_LOGGED_IN
```

## 2. Вход

```
ClientLoginForm
  → loginClient (phone XOR email)
  → POST /api/client/login
  → LoginClientUseCase
  → token + client snapshot
```

Неверный пароль → 401 `UnauthorizedException`.

## 3. Bootstrap приложения

```
MainLayout mount
  → userStore.initFromStorage()
  → если token: fetchClientProfile()
  → GET /api/client/profile
  → emit CLIENT_PROFILE_CHANGED
```

## 4. Адресная книга

```
ClientAddressesManager
  → addClientAddress / deleteClientAddress
  → POST /api/client/addresses
  → DELETE /api/client/addresses/{id}
  → обновление addresses[] + default_address_id в store
  → emit CLIENT_ADDRESS_*
```

Выбор адреса для checkout — локально в store (`selectAddress`), без отдельного API.

## 5. Checkout + Client

```
useCheckout (auth path)
  → useClientReadModel / useClientAddressSelectionModel
  → PATCH /api/checkout/{id}/client с client_id
```

Client BC **не участвует** в этом PATCH — только id из SPA store.

## 6. Сброс пароля

```
forgot: POST /api/client/forgot-password
  → token в CLN_password_reset_tokens
  → email/log со ссылкой на SPA /reset-password?token=

change: ClientResetPasswordPage
  → POST /api/client/change-password
  → ChangePasswordWithTokenUseCase
  → clearAuth() на фронте
```

## 7. Filament (оператор)

```
/admin/clients
  → список CLN_clients
  → ViewClient ?tab=overview|addresses|consents
```

Без write — регистрация только через SPA.

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | GET profile | register, login, PATCH profile, addresses, password |
| SPA | localStorage + GET profile | clientApi POST/PATCH/DELETE |
| Filament | list + view | **запрещён** |
