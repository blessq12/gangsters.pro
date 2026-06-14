# Checkout — слой домена

Ядро BC: агрегат, слепки блоков, порты и события. Без Laravel, HTTP, Eloquent.

## Агрегат

| Элемент | Смысл |
|---------|--------|
| `Checkout` | Aggregate Root: id, status, cart, client?, delivery?, payment?, timestamps |

### Фабрики и восстановление

- `Checkout::create(CheckoutId)` — новый draft, пустая корзина.
- `Checkout::restore(...)` — гидратация из персистентности (Infrastructure).

### Мутации (только `CheckoutStatus::Draft`)

| Метод | Блок |
|-------|------|
| `upsertCartLine(CartLineSnapshot)` | корзина |
| `removeCartLine(int $productId)` | корзина |
| `setClient(ClientSnapshot)` | клиент |
| `setDelivery(DeliverySnapshot)` | доставка |
| `setPayment(PaymentSnapshot)` | оплата |
| `confirm(): CheckoutConfirmed` | финализация |

### Инварианты

- После `confirmed` любая мутация → `CheckoutAlreadyConfirmedException`.
- `confirm()` требует: непустая корзина, заполнены client, delivery, payment → иначе `CheckoutNotReadyForConfirmationException`.
- Событие `CheckoutConfirmed` записывается в `recordedEvents`, отдаётся через `pullRecordedEvents()`.

## Модули-слепки (Value Objects)

| VO | Смысл |
|----|--------|
| `CheckoutId` | UUID v4 (string) |
| `CartSnapshot` | Список `CartLineSnapshot`, пересчёт `itemsTotal()` |
| `CartLineSnapshot` | productId, productName, quantity, unitPrice, payload? |
| `ClientSnapshot` | `guest(GuestContact)` \| `registered(clientId, ...)` |
| `GuestContact` | name, phone, email? |
| `DeliverySnapshot` | method, address?, comment?, scheduledAt? |
| `DeliveryAddress` | street, house, entrance?, apartment? |
| `PaymentSnapshot` | method, changeFromRubles? |

`CartSnapshot` — immutable updates (`upsertLine`, `removeLine` возвращают новый экземпляр).

## Перечисления

| Enum | Значения |
|------|----------|
| `CheckoutStatus` | `draft`, `confirmed` |
| `ClientKind` | `guest`, `registered` |
| `DeliveryMethod` | `courier`, `pickup` |
| `PaymentMethod` | `cash`, `card_courier`, `card_online` (+ `placementValues()`) |

## Порты

| Порт | Ответственность |
|------|-----------------|
| `CheckoutRepository` | `findById`, `save` |
| `CatalogPricingPort` | `findActiveProductQuote(productId)` → `ProductPriceQuote` или null |

`ProductPriceQuote` — productId, productName, unitPrice (`Money`).

## События

| Событие | Payload |
|---------|---------|
| `CheckoutConfirmed` | checkoutId, cart, client, delivery, payment, occurredAt |

## Исключения

| Класс | Когда |
|-------|--------|
| `CheckoutNotFoundException` | Нет строки по id |
| `CheckoutAlreadyConfirmedException` | Мутация или повторный confirm |
| `CheckoutNotReadyForConfirmationException` | Не заполнены блоки |

## Зависимости домена

- `App\Shared\ValueObject\Money` — рубли (int).
- Catalog — только через `CatalogPricingPort` (ACL).

Домен **не знает** про `CHK_checkouts`, HTTP и Pinia.
