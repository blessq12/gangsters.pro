# Checkout — события

## Доменное событие

| Событие | Когда | Класс |
|---------|--------|-------|
| `CheckoutConfirmed` | Успешный `Checkout::confirm()` | `App\Domain\Checkout\Event\CheckoutConfirmed` |

Payload: полные слепки cart, client, delivery, payment + `checkoutId`, `occurredAt`.

Не implements Laravel contracts — plain PHP object, дispatch через `Illuminate\Support\Facades\Event`.

## Подписчики

| Listener | Действие | Состояние |
|----------|----------|-----------|
| `OnCheckoutConfirmed` | — | **Заглушка**; Order BC должен создать заказ |

Регистрация: `CheckoutServiceProvider::boot()`.

## События SPA (не BC Checkout)

| Событие | Когда |
|---------|--------|
| `order.created` (`DOMAIN_EVENTS.ORDER_CREATED`) | После успешного `confirmCheckout` на фронте |

Trigger: `useCheckout.handleConfirmOrder` → `useSessionLifecycleProcess` → reset checkout + clear cart.

## Чего нет

- Outbox / async queue для `CheckoutConfirmed`.
- События изменения блоков (`CartUpdated`, `ClientSet`, …).
- Laravel Eloquent model events на `CHK_Checkout`.
- Интеграционные webhooks.

## Целевой флоу (Order BC)

```
CheckoutConfirmed
  → CreateOrderFromCheckoutHandler (Order Application)
  → persist Order aggregate
  → опционально OrderCreated integration event
```

Сейчас не реализовано.
