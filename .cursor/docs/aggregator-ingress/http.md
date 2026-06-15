# AggregatorIngress — HTTP-слой

Тонкий адаптер: Request → DTO → use case → JSON.

## Endpoint

**Контроллер:** `App\Http\Controllers\Api\IngressController`

| Действие | HTTP | Auth | Use case |
|----------|------|------|----------|
| `store()` | `POST /api/ingress/{partner}/orders` | `X-Ingress-Api-Key` | `ReceiveExternalOrderUseCase` |

`{partner}` — код из [partners.md](partners.md): `stub`, `yandex-eda`, `chibbis`, `kuper`.

## Заголовки

| Заголовок | Обязателен | Смысл |
|-----------|------------|--------|
| `X-Ingress-Api-Key` | да | API-key партнёра из `config/ingress.php` |
| `Content-Type: application/json` | да | тело — JSON по контракту партнёра |

## Ответ 200 (успех / идемпотентность)

```json
{
  "order_id": 42,
  "status": "accepted",
  "order": {
    "id": 42,
    "source": "aggregator",
    "checkout_id": null,
    "partner_code": "yandex-eda",
    "external_order_id": "ye-12345",
    "status": "new",
    "total": 900,
    "created_at": "2026-06-15T12:00:00+00:00",
    "client": { "kind": "guest", "name": "...", "phone": "..." },
    "delivery": { "method": "courier", "address": { ... } },
    "payment": { "method": "card" },
    "items": [ ... ]
  }
}
```

## Коды ошибок

| HTTP | Exception / причина |
|------|---------------------|
| 401 | `IngressAuthenticationFailedException` |
| 404 | `PartnerNotConfiguredException` |
| 422 | `UnknownPartnerSkuException`, `IngressInvariantViolation`, `OrderInvariantViolation` |

## Handler

Обработка в `app/Exceptions/Handler.php` для префикса `api/*`.

## Тесты

| Файл | Что проверяет |
|------|----------------|
| `tests/Feature/IngressReceiveExternalOrderTest.php` | e2e stub: create + idempotent |
| `tests/Unit/AggregatorIngress/IngressPartnerAdaptersTest.php` | map() трёх партнёров |
