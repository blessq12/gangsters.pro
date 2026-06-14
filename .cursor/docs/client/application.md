# Client — слой приложения

Оркестрация команд: load/save aggregate → domain method → present JSON (+ token при auth).

## Активные сценарии

| Use case | DTO | Назначение |
|----------|-----|------------|
| `RegisterClientUseCase` | `RegisterClientDto` | Регистрация + token |
| `LoginClientUseCase` | `LoginClientDto` | Вход по phone **или** email + token |
| `GetClientProfileUseCase` | — (int clientId) | Профиль авторизованного |
| `UpdateClientProfileUseCase` | `UpdateClientProfileDto` | Обновление профиля |
| `AddClientAddressUseCase` | `AddClientAddressDto` | Добавить адрес |
| `DeleteClientAddressUseCase` | `DeleteClientAddressDto` | Удалить адрес |
| `RequestPasswordResetUseCase` | `RequestPasswordResetDto` | Запрос письма (всегда 200-ответ) |
| `ChangePasswordWithTokenUseCase` | `ChangePasswordWithTokenDto` | Смена пароля по токену |

Расположение: `app/Application/Client/useCases/`.

## Общий шаблон команд

1. Загрузка агрегата через `ClientRepository` (или создание при register).
2. Проверки уникальности phone/email где нужно.
3. Вызов метода агрегата.
4. `repository->save()`.
5. `ClientPresenter::present()` или `presentWithToken()`.

## Детали по сценариям

### `RegisterClientUseCase`

- `PhoneNumber::fromRaw`, нормализация email.
- `existsByPhone` / `existsByEmail` → `ClientAlreadyExistsException`.
- `Hash::make` пароля до домена.
- После save: `markRegistered()` → dispatch `ClientRegistered`.
- `ClientAuthTokenPort::issueToken()`.

### `LoginClientUseCase`

- Ровно один идентификатор: phone или email (не оба).
- Неверные credentials → `UnauthorizedException` (401), не 404.
- `Hash::check` против `passwordHash()` агрегата.

### `RequestPasswordResetUseCase`

- Если email найден — store token + notify.
- Ответ одинаковый при отсутствии email (не раскрывать регистрацию).

### `ChangePasswordWithTokenUseCase`

- `resolveEmailByToken` → смена пароля → `delete` token.

## Presenter

`ClientPresenter` — контракт ответа API:

```json
{
  "client": {
    "id": 1,
    "name": "Иван",
    "phone": "9123456789",
    "email": "ivan@example.com",
    "birth_date": null,
    "consent_personal_data": true,
    "consent_marketing": false,
    "addresses": [
      {
        "id": 1,
        "type": null,
        "title": null,
        "street": "Ленина",
        "house": "10",
        "entrance": null,
        "apartment": null,
        "comment": null,
        "is_default": true
      }
    ],
    "default_address_id": 1
  },
  "token": "..."
}
```

`token` только в register/login.

## Common Exceptions

`app/Application/Common/Exceptions/`:

| Класс | HTTP |
|-------|------|
| `ApiException` | базовый (status в конструкторе) |
| `UnauthorizedException` | 401 |

## Чего нет

- Query-список клиентов для API (только Filament).
- Revoke token / logout use case.
- Listener на `ClientRegistered` (интеграции нет).
- Transactional outbox.
