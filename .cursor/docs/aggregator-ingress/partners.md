# AggregatorIngress — контракты партнёров

Шаблоны тел webhook. При получении официальной спецификации правится **только** соответствующий `*IngressPartnerAdapter`.

Общее для всех:

- Endpoint: `POST /api/ingress/{partner}/orders`
- Header: `X-Ingress-Api-Key: <секрет из config>`
- Идемпотентность по ключу заказа партнёра (см. таблицу ниже)
- Строки заказа содержат **SKU партнёра** → резолв через `ING_partner_sku_bindings`

## Сводка

| Партнёр | `{partner}` | Ключ заказа | Массив позиций | Цена позиции |
|---------|-------------|-------------|----------------|--------------|
| Stub (dev) | `stub` | `external_order_id` | `lines[].partner_sku` | `unit_price_rubles` |
| Яндекс Еда | `yandex-eda` | `order_id` | `items[].id` | `price_rubles` |
| Чиббис | `chibbis` | `orderId` | `products[].vendorCode` | `price` (руб) |
| Купер | `kuper` | `order.uuid` | `positions[].id` | `price_kopecks` |

---

## Stub (разработка)

```json
{
  "external_order_id": "stub-001",
  "placed_at": "2026-06-15T12:00:00+00:00",
  "client": {
    "name": "Тест Клиент",
    "phone": "+79990001122"
  },
  "delivery": {
    "method": "pickup"
  },
  "payment": {
    "method": "card_online"
  },
  "lines": [
    {
      "partner_sku": "STUB-SKU-1",
      "quantity": 2,
      "unit_price_rubles": 500
    }
  ]
}
```

`delivery.method`: `courier` | `pickup`  
`payment.method`: `cash` | `card_courier` | `card_online`

---

## Яндекс Еда

```json
{
  "order_id": "ye-12345",
  "created_at": "2026-06-15T12:00:00+03:00",
  "customer": {
    "name": "Иван",
    "phone": "+79990001122",
    "email": "ivan@example.com"
  },
  "delivery": {
    "type": "courier",
    "address": {
      "street": "ул. Ленина",
      "house": "10",
      "entrance": "2",
      "apartment": "15",
      "comment": "домофон не работает"
    },
    "scheduled_at": "2026-06-15T13:00:00+03:00"
  },
  "payment": {
    "type": "card_online",
    "change_from_rubles": null
  },
  "items": [
    {
      "id": "YE-MENU-001",
      "quantity": 2,
      "price_rubles": 450
    }
  ]
}
```

`delivery.type`: `courier` | `pickup`  
`payment.type`: `card_online` | `card_courier` | `cash`

Адаптер: `YandexEdaIngressPartnerAdapter`.

---

## Чиббис

```json
{
  "orderId": "ch-999",
  "createdAt": "2026-06-15T12:00:00+00:00",
  "client": {
    "fullName": "Пётр",
    "phoneNumber": "+79990002233",
    "email": null
  },
  "deliveryType": "delivery",
  "address": {
    "street": "Мира",
    "building": "5",
    "entrance": "1",
    "apartment": "42",
    "comment": "позвонить"
  },
  "deliveryTime": "2026-06-15T13:30:00+00:00",
  "paymentType": "online",
  "changeFrom": null,
  "products": [
    {
      "vendorCode": "CH-VENDOR-12",
      "amount": 1,
      "price": 350
    }
  ]
}
```

`deliveryType`: `delivery` | `pickup`  
`paymentType`: `online` | `cash` | `card_courier`

Адаптер: `ChibbisIngressPartnerAdapter`.

---

## Купер

```json
{
  "order": {
    "uuid": "kp-uuid-001",
    "created_at": "2026-06-15T14:00:00+00:00"
  },
  "user": {
    "name": "Анна",
    "phone": "+79990003344"
  },
  "shipment": {
    "type": "courier",
    "address": {
      "street": "Советская",
      "house": "3",
      "apartment": "7",
      "doorphone": "7"
    },
    "comment": "оставить у двери",
    "scheduled_at": null
  },
  "payment": {
    "method": "prepaid"
  },
  "positions": [
    {
      "id": "KP-EXT-55",
      "quantity": 3,
      "price_kopecks": 25000
    }
  ]
}
```

`shipment.type`: `courier` | `pickup`  
`payment.method`: `prepaid` | `cash` | `card_courier`  
`prepaid` → `card_online` в Order snapshot.

Адаптер: `KuperIngressPartnerAdapter`.

---

## Чеклист подключения партнёра

1. [ ] Адаптер реализует `IngressPartnerAdapter`
2. [ ] Запись в `config/ingress.php` + env
3. [ ] Регистрация в `AggregatorIngressServiceProvider`
4. [ ] SKU bindings для всех позиций меню
5. [ ] Unit-тест `map()` с образцом payload
6. [ ] Feature-тест e2e (опционально, по образцу stub)
7. [ ] Обновить этот файл при изменении контракта
