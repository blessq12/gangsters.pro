# Client — слой инфраструктуры

Персистентность агрегата, Sanctum-токены, reset-токены, уведомления.

## Модели (`CLN_*`)

| Модель | Таблица | Смысл |
|--------|---------|--------|
| `CLN_Client` | `CLN_clients` | Клиент (Authenticatable + HasApiTokens) |
| `CLN_ClientAddress` | `CLN_client_addresses` | Адрес в книге клиента |
| `CLN_PasswordResetToken` | `CLN_password_reset_tokens` | Токен сброса пароля (PK = email) |

### Колонки `CLN_clients`

| Колонка | Смысл |
|---------|--------|
| `id` | bigint PK |
| `name` | string |
| `phone` | string(16), unique, 10 цифр |
| `email` | string, unique |
| `birth_date` | date nullable |
| `password` | bcrypt hash (без cast `hashed` — hash в Application) |
| `consent_personal_data` | bool |
| `consent_marketing` | bool |
| `created_at`, `updated_at` | timestamps |

### Колонки `CLN_client_addresses`

| Колонка | Смысл |
|---------|--------|
| `id` | bigint PK |
| `client_id` | FK → `CLN_clients` |
| `type` | string nullable |
| `title` | string nullable |
| `street`, `house` | string |
| `entrance`, `apartment` | string nullable |
| `comment` | text nullable |
| `is_default` | bool |

### Колонки `CLN_password_reset_tokens`

| Колонка | Смысл |
|---------|--------|
| `email` | PK |
| `token` | bcrypt hash plain token |
| `created_at` | TTL из `config('auth.password_reset_token_ttl_minutes')` |

Миграция: `database/migrations/2026_06_14_180000_create_cln_client_tables.php`.

## Mapper

`ClientMapper`:

- `toDomain(CLN_Client)` → `Client::restore(...)` + addresses.
- `toClientPersistence(Client)` → массив для Eloquent.
- `toAddressPersistence(ClientAddress, clientId)` → массив адреса.

Password читается через `getRawOriginal('password')` при гидратации.

## Репозитории

| Класс | Порт |
|-------|------|
| `EloquentClientRepository` | `ClientRepository` |

`save()` — DB transaction:

- insert/update клиента;
- upsert адресов, assign id новым;
- delete адресов, отсутствующих в агрегате.

## Порты (адаптеры)

| Класс | Порт |
|-------|------|
| `SanctumClientAuthTokenAdapter` | `ClientAuthTokenPort` |
| `EloquentClientPasswordResetTokenStore` | `ClientPasswordResetTokenStorePort` |
| `ClientPasswordResetNotifier` | `ClientPasswordResetNotifierPort` |

Reset URL: `{client_frontend_url}/reset-password?token=...`.

При `mail.default=log` ссылка пишется в лог.

## Композиция

`ClientServiceProvider` (`config/app.php`):

- bind `ClientRepository`
- bind `ClientAuthTokenPort`
- bind `ClientPasswordResetTokenStorePort`
- bind `ClientPasswordResetNotifierPort`

`config/auth.php` — provider `clients` → `CLN_Client`.

## Сидирование

Отдельного seeder **нет** — клиенты создаются через SPA register.
