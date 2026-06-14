# Order — события

Order BC **не публикует** доменных событий при создании (техдолг: `OrderCreated` для уведомлений/аналитики).

## Входящие

| Событие | Источник | Обработчик |
|---------|----------|------------|
| `CheckoutConfirmed` | Checkout BC | `OnCheckoutConfirmed` → `CreateOrderUseCase` |

Подписка: `OrderServiceProvider::boot()`.

## Payload `CheckoutConfirmed`

Содержит слепки cart, client, delivery, payment и `checkoutId` — маппится ACL `CheckoutConfirmedOrderSnapshotMapper`.

## Исходящие (план)

| Событие | Когда |
|---------|--------|
| `OrderStatusChanged` | при реализации смены статуса |
| `OrderCreated` | опционально после save (email, push) |

Сейчас email `client-order-confirmation.blade.php` на фронте/legacy не подключён к listener.
