# Checkout BC — удалён (2026-06)

**Bounded context Checkout удалён.** Функциональность перенесена:

| Было (Checkout) | Стало |
|-----------------|-------|
| `CHK_checkouts` + PATCH wizard | Local draft (Pinia + `sessionStorage`) |
| `POST checkout/{id}/confirm` | `POST /api/orders` (`PlaceOrderUseCase`) |
| Preview через checkout entity | `POST /api/order-drafts/preview` |
| `CheckoutConfirmed` event | Прямой вызов `CreateOrderUseCase` |
| `GetCatalog` + отдельные fetch | `GET /api/storefront/bootstrap` |

## Куда смотреть

| Тема | Документ |
|------|----------|
| Bootstrap витрины | [storefront/overview.md](../storefront/overview.md) |
| OrderDraft + Place | [order/application.md](../order/application.md#orderdraft-сайт-in-memory) |
| HTTP API | [order/http.md](../order/http.md) |
| SPA draft | `resources/js/features/checkout/checkoutSessionService.js` |
| Потоки | [order/flow.md](../order/flow.md) |

## Удалённые пути

```
app/Domain/Checkout/
app/Application/Checkout/
app/Infrastructure/Checkout/
app/Filament/Checkout/
app/Http/Controllers/Api/CheckoutController.php
routes: /api/checkout/*
```

Остальные файлы в этой папке (`domain.md`, `flow.md`, …) — **архив**; не обновляются.
