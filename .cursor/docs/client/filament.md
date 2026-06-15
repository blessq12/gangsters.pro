# Client — Filament (оператор)

Read-only контур: оператор **только смотрит** клиентов из `CLN_clients`. Создание, редактирование и удаление запрещены — регистрация только через SPA.

## Точка входа

| Страница | Класс | URL |
|----------|-------|-----|
| Список | `ListClients` | `/admin/clients` |
| Просмотр | `ViewClient` | `/admin/clients/{id}?tab={ключ}` |

Навигация панели: **«Клиенты»** (`ClientResource`, `navigationSort = 26`, иконка `Users`).

## Ресурс

| Класс | Модель | Slug |
|-------|--------|------|
| `ClientResource` | `CLN_Client` | `clients` |

Страницы ресурса: только `index` + `view` (нет `create` / `edit`).

### Запреты (Resource)

| Метод | Значение |
|-------|----------|
| `canCreate()` | `false` |
| `canEdit()` | `false` |
| `canDelete()` | `false` |
| `canDeleteAny()` | `false` |

### Запреты (UI)

- `ListClients::getHeaderActions()` — пусто.
- `ViewClient::getHeaderActions()` — пусто.
- `ClientsTable` — только `ViewAction`, без bulk actions.

## Список (`ClientsTable`)

| Колонка | Источник |
|---------|----------|
| ID | `id` |
| Имя | `name` (searchable) |
| Телефон | `phone` → `ClientProfileReader::formatPhone()` |
| Email | `email` (searchable) |
| Адресов | `addresses_count` (counts relation) |
| Маркетинг | `consent_marketing` (скрыта по умолчанию) |
| Зарегистрирован | `created_at` |

Сортировка по умолчанию: `created_at desc`.

## Просмотр (`ViewClient`)

Базовый класс: `ViewRecord`. Форма disabled.

`mutateFormDataBeforeFill()` → `ClientProfileReader::formDataFromRecord()`.

### Табы по семантике BC

Схема: `ClientViewSchema::configure($schema, 'activeClientViewTab')`.

| Ключ `?tab=` | UI-метка | Блок | Поля |
|--------------|----------|------|------|
| `overview` | Профиль | профиль | id, name, phone, email, birth_date, created_at |
| `addresses` | Адреса | адресная книга | count + repeater адресов |
| `consents` | Согласия | compliance | consent_personal_data, consent_marketing |

Дефолтный таб: `overview`. Валидация — `ViewClient::VALID_TABS` + `ensureDefaultViewTab()`.

### Переключение табов (важно)

`Tabs::livewireProperty('activeClientViewTab')` + `#[Url(as: 'tab')]`.

**Обязательно:** ассоциативные ключи в `ClientViewSchema`:

```php
->tabs([
    'overview' => Tab::make('overview')->label('Профиль')...,
    'addresses' => Tab::make('addresses')->label('Адреса')...,
    'consents' => Tab::make('consents')->label('Согласия')...,
])
```

Эталон UI табов: `OrderViewSchema`, `ViewOrder`.

## Чтение профиля (`ClientProfileReader`)

`app/Filament/Client/Support/ClientProfileReader.php`

- Вход: `CLN_Client` с `addresses` relation.
- Телефон форматируется `+7 (XXX) XXX-XX-XX`.
- Bool → «Да» / «Нет».

Filament **не** использует Domain/Application Client — только Eloquent (read-only UI).

## Структура кода

```
app/Filament/Client/
  Resources/
    ClientResource.php
    Pages/
      ListClients.php
      ViewClient.php
    Schemas/
      ClientViewSchema.php
    Tables/
      ClientsTable.php
  Support/
    ClientProfileReader.php
```

## Регистрация

`AdminPanelProvider::panel()->resources([..., ClientResource::class])`.

## Принцип

| Контур | Read | Write |
|--------|------|-------|
| Filament | список + просмотр | **запрещён** |
| Публичный API | GET profile | register, login, PATCH, addresses |
| SPA | profile в store | формы клиента |

## Чего нет

- Редактирование профиля/адресов из админки.
- Связь с Order в UI Filament (заказы — отдельный hub).
- Виджеты hub-таблиц.
