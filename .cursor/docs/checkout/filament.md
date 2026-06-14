# Checkout — Filament (оператор)

Read-only контур: оператор **только смотрит** объекты оформления из `CHK_checkouts`. Создание, редактирование и удаление запрещены на уровне ресурса и UI.

## Точка входа

| Страница | Класс | URL |
|----------|-------|-----|
| Список | `ListCheckouts` | `/admin/checkouts` |
| Просмотр | `ViewCheckout` | `/admin/checkouts/{uuid}?tab={ключ}` |

Навигация панели: **«Оформления»** (`CheckoutResource`, `navigationSort = 25`, иконка `ClipboardDocumentList`).

## Ресурс

| Класс | Модель | Slug |
|-------|--------|------|
| `CheckoutResource` | `CHK_Checkout` | `checkouts` |

Страницы ресурса: только `index` + `view` (нет `create` / `edit`).

### Запреты (Resource)

| Метод | Значение |
|-------|----------|
| `canCreate()` | `false` |
| `canEdit()` | `false` |
| `canDelete()` | `false` |
| `canDeleteAny()` | `false` |

### Запреты (UI)

- `ListCheckouts::getHeaderActions()` — пусто (нет «Создать»).
- `ViewCheckout::getHeaderActions()` — пусто (нет «Редактировать»).
- `CheckoutsTable` — только `ViewAction`, без bulk actions.

## Список (`CheckoutsTable`)

| Колонка | Источник |
|---------|----------|
| ID | `id` (UUID, searchable, copyable) |
| Статус | `status` → badge + `CheckoutSnapshotReader::statusLabel()` |
| Позиций | `count(cart_snapshot.lines)` |
| Сумма | сумма `line_total_rubles` по строкам корзины |
| Клиент | `client_snapshot` (имя гостя / `Клиент #id`) |
| Доставка | `delivery_snapshot.method` → русская подпись |
| Оплата | `payment_snapshot.method` → русская подпись |
| Создано / Подтверждено | `created_at`, `confirmed_at` |

Фильтр: `SelectFilter` по `status` (`draft` / `confirmed`).

Сортировка по умолчанию: `created_at desc`.

## Просмотр (`ViewCheckout`)

Базовый класс: `ViewRecord`. Форма **disabled** (`defaultForm` → `operation('view')`).

Данные не читаются из колонок модели напрямую в полях — `mutateFormDataBeforeFill()` подменяет state через `CheckoutSnapshotReader::formDataFromRecord()`.

### Табы по семантике BC

Ключ таба = блок агрегата Checkout. Схема: `CheckoutViewSchema::configure($schema, 'activeCheckoutViewTab')`.

| Ключ `?tab=` | UI-метка | Блок агрегата | Поля |
|--------------|----------|---------------|------|
| `overview` | Общее | метаданные | id, status, created_at, confirmed_at |
| `cart` | Корзина | `CartSnapshot` | repeater позиций, сумма товаров |
| `client` | Клиент | `ClientSnapshot` | kind, client_id, name, phone, email |
| `delivery` | Доставка | `DeliverySnapshot` | method, адрес, comment, scheduled_at |
| `payment` | Оплата | `PaymentSnapshot` | method, change_from |

Дефолтный таб: `overview`. Валидация ключа — `ViewCheckout::VALID_TABS` + `ensureDefaultViewTab()` в `mount()`.

### Переключение табов (важно)

Используется `Tabs::livewireProperty('activeCheckoutViewTab')` + Livewire `#[Url(as: 'tab')]`.

**Обязательно:** табы в `CheckoutViewSchema` задаются **ассоциативным массивом**:

```php
->tabs([
    'overview' => Tab::make('overview')->label('Общее')...,
    'cart' => Tab::make('cart')->label('Корзина')...,
    // ...
])
```

При `livewireProperty` Filament сравнивает значение свойства с **ключом массива**, не с `Tab::make(...)`. Список без ключей (`[Tab::make(...), ...]`) даёт ключи `0`, `1`, `2` — активный таб и контент не совпадут (пустая страница). Эталон того же паттерна: `EditProduct` + `RendersCatalogResourceTabs`.

Корзина: `Repeater` с `addable(false)`, `deletable(false)`, `reorderable(false)`.

## Чтение слепков (`CheckoutSnapshotReader`)

`app/Filament/Checkout/Support/CheckoutSnapshotReader.php`

- Вход: Eloquent `CHK_Checkout`.
- Выход: плоский массив для disabled-формы.
- Enum-значения API → русские подписи (статус, способ доставки/оплаты, kind клиента).
- Деньги форматируются как `1 234 ₽`.

Filament-слой **не** использует Domain/Application Checkout — только Infrastructure-модель и JSON-колонки (осознанное упрощение read-only UI).

## Структура кода

```
app/Filament/Checkout/
  Resources/
    CheckoutResource.php
    Pages/
      ListCheckouts.php
      ViewCheckout.php
    Schemas/
      CheckoutViewSchema.php
    Tables/
      CheckoutsTable.php
  Support/
    CheckoutSnapshotReader.php
```

## Регистрация

`AdminPanelProvider::panel()->resources([..., CheckoutResource::class])`.

Роуты Filament:

- `GET admin/checkouts` — `filament.admin.resources.checkouts.index`
- `GET admin/checkouts/{record}` — `filament.admin.resources.checkouts.view`

## Принцип

| Контур | Read | Write |
|--------|------|-------|
| Filament | список + просмотр по табам | **запрещён** |
| Публичный API | — | `/api/checkout/*` |
| SPA | — | визард оформления |

Оператор не создаёт и не правит черновики — только аудит и поддержка клиентов.

## Чего нет

- Relation managers, hub-таблицы, reorder.
- Infolist (используется disabled Form + `ViewRecord`).
- Синхронизации с Domain Events (`CheckoutConfirmed` Filament не слушает).
