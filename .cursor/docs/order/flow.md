# Order — потоки

## Создание заказа (happy path)

```mermaid
sequenceDiagram
    participant SPA
    participant CheckoutAPI
    participant CheckoutUC as ConfirmCheckoutUseCase
    participant Event as CheckoutConfirmed
    participant OrderH as OnCheckoutConfirmed
    participant OrderUC as CreateOrderUseCase
    participant DB as ORD_orders

    SPA->>CheckoutAPI: POST /api/checkout/{id}/confirm
    CheckoutUC->>Event: dispatch
    Event->>OrderH: handle
    OrderH->>OrderUC: CreateOrderDto
    OrderUC->>DB: insert (idempotent)
    CheckoutAPI-->>SPA: checkout + order
```

## История заказов (SPA)

```mermaid
sequenceDiagram
    participant SPA
    participant OrderAPI
    participant UC as ListClientOrdersUseCase
    participant DB as ORD_orders

    SPA->>OrderAPI: GET /api/order (Bearer)
    OrderAPI->>UC: clientId
    UC->>DB: where client_id order by created_at desc
    OrderAPI-->>SPA: { data: [...] }
```

Триггер загрузки: `useOrdersReadModel({ autoload: true })` в профиле клиента.

## Идемпотентность

Повторный `confirm` на тот же checkout не создаёт второй заказ — `findByCheckoutId` в `CreateOrderUseCase`.

## Гостевые заказы

`client_id` в `ORD_orders` = null → не попадают в `GET /api/order` (только registered).

Оператор видит гостевые заказы в Filament `/admin/orders`.
