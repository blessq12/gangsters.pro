# Client — обзор BC

**Роль:** учётная запись покупателя — регистрация, вход, профиль, адресная книга, сброс пароля. Публичный write/read API для SPA; оператор смотрит клиентов в Filament read-only.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Клиент (агрегат)** | Профиль + адресная книга, id — auto-increment |
| **Профиль** | Имя, телефон (10 цифр РФ), email, дата рождения, согласия |
| **Адрес** | Улица, дом, подъезд, квартира, комментарий; флаг `is_default` |
| **Токен доступа** | Sanctum Bearer (`client-api`), выдаётся при register/login |
| **Сброс пароля** | Токен в `CLN_password_reset_tokens` + ссылка на SPA |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Регистрация, auth, профиль, CRUD адресов | Оформление заказа (Checkout хранит **слепок** клиента) |
| Хранение в `CLN_*` | Создание заказа (Order BC) |
| Публичный API `/api/client/*` | Legacy `/api/shopping/*` (не реализован) |
| Filament read-only просмотр | Админ-пользователи (`users` — отдельная таблица для Filament admin) |

Checkout при `PATCH .../client` принимает `client_id` **без проверки** существования клиента в Client BC — техдолг.

## Хранение

Таблицы `CLN_clients`, `CLN_client_addresses`, `CLN_password_reset_tokens`. Телефон — **10 цифр** без ведущей 7/8 (согласовано с `PhoneNumber` и фронтом `normalizeRuPhoneDigits`).

## Пути в коде

| Слой | Client |
|------|--------|
| Domain | `app/Domain/Client/` |
| Application | `app/Application/Client/` |
| Infrastructure | `app/Infrastructure/Client/` |
| HTTP | `app/Http/Controllers/Api/ClientController.php` |
| Filament | `app/Filament/Client/` — read-only список и просмотр |
| SPA | `resources/js/stores/userStore.js`, `resources/js/api/clientApi.js`, `resources/js/components/client/` |

## Аудит (состояние на 2026-06-14)

### Готово

- Полная вертикаль Domain → Application → Infrastructure → HTTP (8 сценариев).
- Sanctum Bearer на защищённых endpoint'ах (`auth:sanctum`).
- Агрегат: профиль + адресная книга, default-адрес через `is_default`.
- Filament: список + просмотр с табами (профиль / адреса / согласия).
- `ClientServiceProvider`, provider `clients` в `config/auth.php`.
- Handler: маппинг Client-исключений + `ClientUnauthorizedAccessDetected`.

### Пробелы / техдолг

| # | Тема | Детали |
|---|------|--------|
| 1 | **Checkout ACL** | `SetCheckoutClientUseCase` не валидирует `client_id` через Client BC |
| 2 | **Тесты** | Нет feature/unit на use case и API |
| 3 | **Legacy `users`** | Таблица `users` — только Filament admin; клиенты в `CLN_clients` |
| 4 | **Logout API** | Нет `POST /api/client/logout` (фронт чистит токен локально) |
| 5 | **Письмо сброса** | Plain-text mail / log; нет HTML-шаблона |
| 6 | **Order BC** | История заказов на фронте ждёт `/api/order` — не реализован |

### Рекомендуемый порядок доработок

1. Port `ClientLookupPort` + проверка `client_id` в Checkout.
2. Feature-тесты: register, login, profile, addresses, password reset.
3. `POST /api/client/logout` (revoke current token).
4. HTML Mailable для сброса пароля.
