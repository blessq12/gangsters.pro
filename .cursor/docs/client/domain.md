# Client — слой домена

Ядро BC: агрегат клиента, адреса, порты auth/reset. Без Laravel, HTTP, Eloquent.

## Агрегат

| Элемент | Смысл |
|---------|--------|
| `Client` | Aggregate Root: профиль, password hash, согласия, список `ClientAddress` |
| `ClientAddress` | Сущность внутри агрегата: адрес доставки с optional id после persist |

### Фабрики и восстановление

- `Client::register(...)` — новый клиент **без id** (id назначает Infrastructure при save).
- `Client::restore(...)` — гидратация из персистентности.
- `ClientAddress::create(...)` — новый адрес без id.
- `ClientAddress::restore(...)` — адрес из БД.

### Мутации

| Метод | Смысл |
|-------|--------|
| `updateProfile(...)` | Имя, телефон, email, birth_date, согласия |
| `changePassword(string $hash)` | Смена password hash |
| `addAddress(ClientAddress)` | Добавление; при `makeDefault` или пустой книге — сброс default у остальных |
| `removeAddress(ClientAddressId)` | Удаление; если удалили default — первый оставшийся становится default |
| `assignId(ClientId)` | После insert клиента |
| `assignAddressId(address, id)` | После insert адреса |
| `markRegistered()` | Записать `ClientRegistered` после появления id |

### Инварианты

- `id()` бросает `LogicException`, если агрегат ещё не сохранён.
- Удаление несуществующего адреса → `ClientAddressNotFoundException`.
- Телефон валидируется VO `PhoneNumber` (ровно 10 цифр).

## Value Objects

| VO | Смысл |
|----|--------|
| `ClientId` | Положительный int |
| `ClientAddressId` | Положительный int |
| `PhoneNumber` | Нормализация РФ-номера (`fromRaw`, `normalizeDigits`) |

## Порты

| Порт | Ответственность |
|------|-----------------|
| `ClientRepository` | `findById`, `findByPhone`, `findByEmail`, `exists*`, `save` |
| `ClientAuthTokenPort` | `issueToken(ClientId)` → plain Sanctum token |
| `ClientPasswordResetTokenStorePort` | store / resolve / delete reset token |
| `ClientPasswordResetNotifierPort` | Отправка ссылки на SPA |

## События

| Событие | Payload |
|---------|---------|
| `ClientRegistered` | `clientId` |
| `ClientUnauthorizedAccessDetected` | path, method, ip, userAgent (для Handler на `api/client/*`) |

## Исключения

| Класс | Когда |
|-------|--------|
| `ClientNotFoundException` | Нет клиента по id / email / phone |
| `ClientAlreadyExistsException` | Дубликат phone или email при register/update |
| `ClientAddressNotFoundException` | Удаление несуществующего адреса |
| `InvalidPasswordResetTokenException` | Просроченный или неверный токен сброса |

## Зависимости домена

- Нет прямых зависимостей на Order, Catalog.
- Order BC ссылается на клиента через **слепок** в `ORD_orders` (вне домена Client).

Домен **не знает** про `CLN_*`, Sanctum, Pinia.
