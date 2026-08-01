# Design: управляемый Checkout (Quote / Place)

Дата: 2026-08-01  
Статус: design for review  
Контекст: Order BC + SPA checkout  
Связанный canvas: order-creation-flow

## 1. Проблема

Сейчас один backend-pipeline на preview/place смешивает:

1. цены каталога;
2. модификацию корзины акциями (gift/complement);
3. геокод;
4. тариф/зону доставки;
5. UX-состояние визарда (`suggested_step`, `can_confirm`, …).

Плюс фейковый Domain-aggregate `OrderDraft` (in-memory, без персистентности).

Итог: любое изменение подарка/зоны/UI-шага трогает один God-object.

## 2. Цель

Сделать создание заказа **управляемым**:

- два внешних сценария: **quote** (пересчёт) и **place** (фиксация);
- внутри quote — **три независимых калькулятора** с чёткими границами;
- **визард только на FE**;
- Domain содержит только финальный `Order` (+ ports), без `OrderDraft`.

## 3. Нецели

- FSM / персистентная серверная корзина;
- смена бизнес-правил Promotion;
- полный redesign UI визарда (только смена источника `canConfirm`);
- изменение idempotency `client_request_id` / `CreateOrderUseCase`.

## 4. Роли слоёв

| Слой | Владеет | Не владеет |
|------|---------|------------|
| FE | UI-шаги, session, черновые формы, выбранный gift id, `canConfirm` по локальным правилам + данным quote | Правдой цен/акций/зоны |
| Application Checkout | Quote/Place orchestration, 3 калькулятора, assert, map → CreateOrder | Шагами визарда |
| Domain Order | `Order`, snapshots заказа, ports | Черновиком / wizard |
| Promotion | Правила gift/complement/delivery benefits | HTTP checkout |
| Content | Delivery config + geocoder port | Составом корзины |

## 5. HTTP

| Было | Станет |
|------|--------|
| `POST /api/order-drafts/preview` | `POST /api/orders/quote` |
| `POST /api/orders` | `POST /api/orders` |

Контроллер: расширить/заменить на методы `quote` + `store` (предпочтительно `OrderController` или тонкий `CheckoutController` в Http).  
`OrderDraftController` и `/order-drafts/*` — удалить.

Validation: `QuoteOrderRequest` / `PlaceOrderRequest`.

## 6. Внутренний поток Quote

```
CheckoutInput
  → 1. Pricing
  → 2. Benefits
  → 3. DeliveryCost
  → CheckoutQuote (application model)
  → Presenter JSON
```

### 6.1 Pricing

- **Вход:** user lines (`product_id`, `qty`, payload без system kinds).
- **Делает:** публичные цены через `CatalogPricingPort`.
- **Выход:** priced user lines + subtotal.
- **Не делает:** gift/complement, geo, delivery fee, wizard.

### 6.2 Benefits

- **Вход:** priced cart + `selected_gift_product_id` + канал (courier/pickup).
- **Делает:** sync complement lines; sync gift (price 0 / remove); promo_state; benefits_progress (через Promotion evaluate **без** delivery coords, если fee считается в шаге 3 — см. ниже).
- **Выход:** cart с system lines + promo read-model.
- **Не делает:** geocode, UI wizard.

*Уточнение по delivery fee в Promotion:* сейчас `EvaluatePromotionBenefits` принимает lat/lng. В TO-BE:

- либо шаг 3 вызывает Promotion только за delivery pricing после geo;
- либо Benefits считает gift/complement, а DeliveryCost отдельно дергает delivery-fee часть policy.

Предпочтение: **Benefits = gift/complement/progress; DeliveryCost = zone/fee** (даже если внутри один Promotion service с разными entrypoints).

### 6.3 DeliveryCost

- **Вход:** delivery method + address; при courier — geocode здесь.
- **Делает:** lat/lng; `delivery_pricing` (fee, zone, surcharges).
- **Выход:** enriched delivery + pricing block.
- **Не делает:** состав корзины, wizard.
- **Place:** как сейчас, lat/lng **не** обязаны попадать в сохранённый `Order` address (поведение не менять без отдельного решения).

## 7. Поток Place

```
PlaceOrderInput (client_request_id + CheckoutInput)
  → тот же Pricing → Benefits → DeliveryCost
  → Assert (cart/client/delivery/payment + gift validity)
  → Map → CreateOrderDto
  → CreateOrderUseCase
  → { order } 201
```

Один shared calculator path с quote — без дублирования правил.

## 8. Application layout (целевой)

```
app/Application/Order/Checkout/
  useCases/QuoteOrderUseCase.php
  useCases/PlaceOrderUseCase.php
  DTO/CheckoutInput.php
  DTO/PlaceOrderInput.php
  Model/CheckoutQuote.php          # результат трёх шагов (не Domain entity)
  Services/Pricing/PriceCheckoutCart.php
  Services/Benefits/ApplyCheckoutBenefits.php
  Services/Delivery/ResolveCheckoutDeliveryCost.php
  Services/AssertCheckoutReady.php
  Mapper/CheckoutToCreateOrderMapper.php
  Presenter/CheckoutQuotePresenter.php
```

Удалить после переноса:

- `Domain/Order/OrderDraft/**`
- `Application/Order/OrderDraft/**`
- `OrderDraftController`, старые FormRequests

Exceptions:

- `CheckoutNotReadyException`
- `CheckoutGiftBenefitViolationException`

(вместо OrderDraft* ; Handler мапит на те же 422.)

## 9. Контракт JSON Quote

### Обязательно сохранить (FE)

- `cart` (items, totals, `promo_state`)
- `client` / `delivery` / `payment`
- `delivery_pricing`
- `benefits_progress`
- `order_preview` (или эквивалент тоталов/gift CTA, если FE ещё читает)

### Убрать с бэка (после FE-правки)

- `wizard.suggested_step`
- `wizard.can_confirm`
- `wizard.missing_blocks`

**Миграция контракта (два этапа):**

| Этап | Backend | Frontend |
|------|---------|----------|
| A | URL `/orders/quote`, структура как preview, `wizard` ещё отдаётся | только смена URL + rename клиентских символов |
| B | `wizard` удалён из ответа | `canConfirm` / missing — локально на FE |

Этап B входит в этот дизайн как цель; внедрение можно сразу после A в том же PR или следующим — зафиксировать при плане.

## 10. Frontend

- API: `quoteOrderRequest` → `POST /api/orders/quote`; place без смены URL.
- Session: `refreshOrderQuote` вместо `refreshOrderDraftPreview`.
- После этапа B: wizard gating без `data.wizard` с сервера (поля формы + quote totals/promo достаточно).

## 11. Порядок внедрения

1. Вынести calculator path в `Application/Order/Checkout` (3 сервиса), quote/place use cases.  
2. Роуты + requests; удалить `/order-drafts`.  
3. Удалить Domain/Application OrderDraft; починить codec/Handler.  
4. FE: URL + rename.  
5. FE: локальный `canConfirm`; убрать `wizard` из presenter.  
6. Smoke: gift/complement/zone totals → place → Order.

## 12. Риски

- Promotion сейчас монолитный evaluate — аккуратно разрезать entrypoints gift vs delivery fee.  
- Большой diff — коммиты по этапам A затем B.  
- FormRequests в WT могут быть уже удалены — восстановить под новыми именами.

## 13. Acceptance

- [ ] Нет `OrderDraft` в Domain/Application/Http  
- [ ] Quote = Pricing → Benefits → DeliveryCost (раздельные сервисы)  
- [ ] Geo только в DeliveryCost  
- [ ] `POST /api/orders/quote` и `POST /api/orders` работают  
- [ ] Нет `/api/order-drafts/*`  
- [ ] После этапа B: бэкенд не отдаёт wizard-state; checkout подтверждаем с FE  
- [ ] Place сохраняет idempotency и CreateOrder semantics

## 14. Решения, зафиксированные заранее

1. Вариант продукта: **A** — checkout остаётся рабочим с серверным quote.  
2. Без FSM Order.  
3. Без Domain-сущности черновика.  
4. Wizard — не ответственность бэкенда (цель этапа B).
