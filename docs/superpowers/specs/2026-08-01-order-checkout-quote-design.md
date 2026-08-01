# Design: OrderDraft → Checkout Quote/Place (без сущности OrderDraft)

Дата: 2026-08-01  
Статус: draft for review  
Контекст: Order BC, SPA checkout

## Цель

Убрать фейковый domain-aggregate `OrderDraft` и HTTP `/order-drafts/*`, сохранив рабочий checkout:

- серверный пересчёт корзины (quote);
- authoritative place заказа.

## Нецели

- FSM / персистентный draft-заказ;
- смена правил Promotion;
- redesign UI визарда;
- изменение семантики `CreateOrderUseCase` / idempotency `client_request_id`.

## Решение

**Подход:** Application Checkout без Domain-сущности `OrderDraft`.

Логика build + benefits + delivery prepare остаётся в Application как сервисы над DTO.  
Domain Order владеет только финальным `Order` (+ ports).

## HTTP

| Было | Станет |
|------|--------|
| `POST /api/order-drafts/preview` | `POST /api/orders/quote` |
| `POST /api/orders` | `POST /api/orders` (без смены роли) |

Контроллер: `OrderController` (или `CheckoutController`) — методы `quote` / `store`.  
`OrderDraftController` — удалить.

Form requests: `QuoteOrderRequest` / `PlaceOrderRequest` (восстановить validation, если файлы уже снесены в WT).

## Application layout

```
app/Application/Order/Checkout/
  useCases/QuoteOrderUseCase.php
  useCases/PlaceOrderUseCase.php
  DTO/CheckoutInput.php
  DTO/PlaceOrderInput.php
  Services/BuildCheckoutFromInput.php
  Services/ProcessCheckoutPipeline.php
  Services/ApplyComplementBenefitLines.php
  Services/ApplyGiftBenefitLines.php
  Services/PrepareCheckoutDeliveryAddress.php
  Services/EvaluateCheckoutBenefits.php          # quote-only read model inputs
  Mapper/CheckoutToCreateOrderMapper.php
  Mapper/CheckoutBenefitsInputMapper.php         # quote-only
  Presenter/CheckoutQuotePresenter.php
  Presenter/CheckoutOrderPreviewPresenter.php
  Support/… (wizard, line classifier, roll counter)
```

Пакет `Application/Order/OrderDraft/` — удалить после переноса.

### Потоки

**Quote**

1. `CheckoutInput` из request  
2. `BuildCheckoutFromInput` (pricing ports, client resolve, gift candidate)  
3. `ProcessCheckoutPipeline(forPlace: false)`  
4. `CheckoutQuotePresenter` → JSON

**Place**

1. `PlaceOrderInput` (`clientRequestId` + `CheckoutInput`)  
2. Build + `ProcessCheckoutPipeline(forPlace: true)` + asserts  
3. `CheckoutToCreateOrderMapper` → `CreateOrderDto`  
4. `CreateOrderUseCase`  
5. `{ order: … }` 201

Shared pipeline обязателен (один источник правды по gift/complement).

## Domain

Удалить целиком:

- `app/Domain/Order/OrderDraft/**`

Исключения переименовать/перенести:

- `OrderDraftNotReadyException` → `CheckoutNotReadyException` (Application или `Domain/Order/Exception`)
- `OrderDraftGiftBenefitViolationException` → `CheckoutGiftBenefitViolationException`

VO черновика не живут в Domain: либо private/application structures внутри Checkout services, либо тонкие immutable DTO в `Application/Order/Checkout/DTO` / `Model`.  
В `CreateOrderDto` по-прежнему только `Order*Snapshot` Domain Order.

`CartLinesSnapshotCodec`: убрать зависимость от `OrderDraft\CartLineSnapshot` (только `OrderLineSnapshot` / выкинуть мёртвый `deserializeToCartLine`).

## Контракт quote (FE-совместимость)

Ответ `POST /orders/quote` **сохраняет текущий shape preview** (поля и вложенность):

- `cart` (items, totals, `promo_state`)
- `client` / `delivery` / `payment`
- `delivery_pricing`
- `benefits_progress`
- `promo_state` (если дублируется — оставить как сейчас)
- `wizard` (`suggested_step`, `can_confirm`, `missing_blocks`)
- `order_preview` (complement/auto lines, gift_summary/cta, totals, benefits)

Ломающий change API на этом шаге **не делаем**.  
Допускается только смена URL.

## Frontend

- `orderDraftApi.js` → `orderApi.js` (или расширить существующий):  
  - `quoteOrderRequest` → `POST /api/orders/quote`  
  - `placeOrderRequest` → `POST /api/orders`
- Переименовать символы `*OrderDraft*` / `refreshOrderDraftPreview` → `*Quote*` / `refreshOrderQuote` в checkout session/store/scheduler.
- Поведение визарда и normalizers — без смены ожидаемых полей ответа.

## Handler / errors

`Handler` мапит новые exception types на те же HTTP-коды, что сейчас для draft (422 + payload).

## Тесты

Минимально после переноса (если поднимется test harness):

- quote возвращает 200 и ключевые ключи JSON;
- place без обязательных блоков → 422;
- place happy-path дергает CreateOrder (feature с БД или unit с fake ports).

## Порядок внедрения

1. Ввести `Application/Order/Checkout/*` (перенос + rename логики).  
2. Новые роуты + controller; старый preview route — redirect или сразу удалить.  
3. Удалить Domain/Application OrderDraft + старый controller.  
4. Починить codec / Handler / AGENTS.  
5. FE: URL + rename вызовов.  
6. Smoke: preview totals/gift → place order.

## Риски

- Большой diff rename — делать атомарно по слоям (BE quote/place зелёные, потом FE).  
- Geocode на place сейчас не пишется в Order address — поведение не менять.  
- Working tree уже может иметь удалённые FormRequests — восстановить в новом имени.

## Acceptance

- [ ] Нет `OrderDraft` в Domain/Application/Http  
- [ ] `POST /api/orders/quote` кормит текущий checkout  
- [ ] `POST /api/orders` создаёт заказ как сейчас  
- [ ] `/api/order-drafts/*` отсутствует  
- [ ] FE не обращается к `order-drafts`
