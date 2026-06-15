# OrderAccountingExport — системы учёта

Контракты внешних API (приблизительная реализация; уточняется по мере интеграции).

## Общий контракт адаптера

```php
interface AccountingSystemAdapter
{
    public function systemCode(): string;
    public function isEnabled(): bool;
    public function supports(OrderCreated $event): bool;
    public function export(OrderCreated $event): ExportResult;
}
```

Сейчас все реализованные адаптеры: `supports() === true` для любого заказа.

---

## stub

**Назначение:** dev / e2e без внешних вызовов.

| Параметр | Значение |
|----------|----------|
| `systemCode()` | `stub` |
| HTTP | нет |
| `external_reference` | `stub-{orderId}` |

Включение: `OAE_STUB_ENABLED=true`.

---

## frontpad

**Документация API:** [Frontpad API (GitHub)](https://github.com/n0rn/frontpad/blob/master/README.md)

### Запрос

| | |
|--|--|
| Method | POST (form-urlencoded) |
| URL | `https://app.frontpad.ru/api/index.php?new_order` |
| Auth | `secret` в теле запроса |

### Маппинг `FrontpadOrderMapper`

| Наше поле | Frontpad |
|-----------|----------|
| `client.phone` | `phone` (только цифры) |
| `client.name` | `name` |
| `client.email` | `mail` |
| `delivery.address.street` | `street` |
| `delivery.address.house` | `home` |
| `delivery.address.entrance` | `pod` |
| `delivery.address.apartment` | `apart` |
| `delivery.comment` + order id | `descr` |
| `delivery.scheduledAt` | `datetime` (`Y-m-d H:i:s`) |
| `payment.method` | `pay` (код из config) |
| `cart.lines[].productId` → binding | `product[n]`, `product_kol[n]`, `product_price[n]` |

Самовывоз (`pickup`): адрес не передаётся.

### Ответ (успех)

```json
{
  "result": "success",
  "order_id": "1000020",
  "order_number": "143"
}
```

`external_reference` = `order_id` или `order_number`.

### Ограничения API

- Лимит: 30 запросов в минуту
- Артикулы товаров должны существовать в Frontpad
- Коды `pay`, `point`, `channel` — из справочников Frontpad

---

## iiko

**Документация API:** [iiko Cloud API](https://api-ru.iiko.services/docs)

### Аутентификация

```
POST /api/1/access_token
{ "apiLogin": "..." }
→ { "token": "..." }
```

### Создание доставки

```
POST /api/1/deliveries/create
Authorization: Bearer {token}
```

### Маппинг `IikoOrderMapper`

| Наше поле | iiko `order` |
|-----------|--------------|
| `client.phone` | `phone` (`+7...`) |
| `client.name` | `customer.name` |
| `delivery.method = courier` | `orderServiceType: DeliveryByCourier` + `deliveryPoint` |
| `delivery.method = pickup` | `orderServiceType: DeliveryByClient` |
| `delivery.address` | `deliveryPoint.address` (street, house, flat) |
| config lat/lon | `deliveryPoint.coordinates` (заглушка) |
| `delivery.scheduledAt` | `completeBefore` |
| order id + comment | `comment` |
| `cart.lines` → binding | `items[]` (`productId`, `type: Product`, `amount`, `price`) |
| `payment` + config | `payments[]` (`paymentTypeKind`, `paymentTypeId`, `sum`, `isProcessedExternally`) |

### Ответ

`orderInfo.id` или `correlationId` → `external_reference`.

Команда **асинхронная** — polling `/api/1/commands/status` пока не реализован.

### Требует настройки в iiko

- `organization_id`, `terminal_group_id`
- UUID типов оплаты (`paymentTypeId`)
- UUID товаров в `product_bindings`
- Опционально: `default_street_id`, координаты по умолчанию

---

## Сравнение

| | Frontpad | iiko |
|--|----------|------|
| Формат | form POST | JSON REST |
| Товар | артикул (строка) | UUID продукта |
| Auth | secret в теле | Bearer token |
| Доставка | поля street/home/… | `deliveryPoint` + service type |
| Async | нет | да (correlationId) |
