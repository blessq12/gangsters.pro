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
  → storefrontStore.fetchBootstrap()
  → userStore.initFromStorage()
  → если token: fetchClientProfile()
  → GET /api/client/profile
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

Выбор адреса для оформления — локально в store (`selectAddress`), попадает в local OrderDraft.

## 5. OrderDraft + Client

```
useCheckout (auth path)
  → useClientReadModel / useClientAddressSelectionModel
  → resolveRegisteredClientId из userStore.profile.id
  → POST /api/order-drafts/preview | POST /api/orders
     client: { client_id, name?, phone?, email? }
```

Адреса клиента **без lat/lng** — geocode на сервере при preview (street + house).

`ClientProfilePort` (Order) подтягивает профиль registered client при сборке draft.

Client BC **не участвует** в preview/place напрямую — только auth API и id из SPA store.

См. [Order SPA](../order/spa.md).

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
