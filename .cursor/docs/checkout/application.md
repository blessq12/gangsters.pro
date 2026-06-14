# Checkout — слой приложения

Оркестрация команд: load aggregate → domain method → save → present JSON.

## Активные сценарии

| Use case | DTO | Назначение |
|----------|-----|------------|
| `CreateCheckoutUseCase` | `CreateCheckoutDto` | Создать draft с новым UUID |
| `UpdateCheckoutCartUseCase` | `UpdateCheckoutCartDto` | Upsert/remove строки корзины (`quantity=0` → удаление) |
| `SetCheckoutClientUseCase` | `SetCheckoutClientDto` | Блок клиента (guest или registered) |
| `SetCheckoutDeliveryUseCase` | `SetCheckoutDeliveryDto` | Блок доставки |
| `SetCheckoutPaymentUseCase` | `SetCheckoutPaymentDto` | Блок оплаты |
| `ConfirmCheckoutUseCase` | `ConfirmCheckoutDto` | Confirm + dispatch Laravel events |

Расположение: `app/Application/Checkout/useCases/`.

## Общий шаблон команд (кроме create)

1. `CheckoutRepository::findById(CheckoutId)`.
2. Если null → `CheckoutNotFoundException`.
3. Вызов метода агрегата.
4. `repository->save()`.
5. `CheckoutPresenter::present()` → JSON-массив.

## Детали по сценариям

### `UpdateCheckoutCartUseCase`

- При `quantity > 0`: `CatalogPricingPort::findActiveProductQuote()`; если null → `InvalidArgumentException`.
- Строит `CartLineSnapshot::fromQuote()`.

### `SetCheckoutClientUseCase`

- `clientId` задан → `ClientSnapshot::registered(...)`.
- Иначе guest: обязательны `name` + `phone`.

### `SetCheckoutDeliveryUseCase`

- `DeliveryMethod::Courier` без адреса → `InvalidArgumentException`.

### `ConfirmCheckoutUseCase`

- `$checkout->confirm()`.
- `save()`.
- `Event::dispatch()` для каждого события из `pullRecordedEvents()`.

## Presenter

`CheckoutPresenter` — единый контракт ответа API:

```json
{
  "checkout_id": "uuid",
  "status": "draft",
  "cart": {
    "items": [
      {
        "product_id": 1,
        "product_name": "...",
        "quantity": 2,
        "unit_price_rubles": 500,
        "line_total_rubles": 1000,
        "payload": null
      }
    ],
    "items_total_rubles": 1000
  },
  "client": null,
  "delivery": null,
  "payment": null,
  "created_at": "2026-06-14T12:00:00+07:00",
  "confirmed_at": null
}
```

## Handler

| Класс | Событие | Состояние |
|-------|---------|-----------|
| `OnCheckoutConfirmed` | `CheckoutConfirmed` | Заглушка под Order BC |

Регистрация: `CheckoutServiceProvider::boot()`.

## DTO

`app/Application/Checkout/DTO/` — readonly input на каждый use case. HTTP-контроллер мапит `FormRequest` → DTO + domain VO (`DeliveryAddress`, enums).

## Чего нет

- Query use case (`GetCheckoutUseCase`) — **пробел**.
- Отдельные Application-сервисы для promotions / delivery pricing.
- Transactional outbox.
