# AggregatorIngress — маршрутизация

Файл: `routes/api.php`.

## API

```php
Route::post('ingress/{partner}/orders', [IngressController::class, 'store']);
```

| Method | URI | Middleware |
|--------|-----|------------|
| POST | `/api/ingress/{partner}/orders` | `api` (без `auth.client`) |

Auth — внутри use case по API-key, не Sanctum.

## Config

`config/ingress.php`:

```php
'partners' => [
    'stub' => ['enabled' => ..., 'api_key' => ...],
    'yandex-eda' => [...],
    'chibbis' => [...],
    'kuper' => [...],
],
```

## Env (`.env.example`)

| Переменная | Партнёр |
|------------|---------|
| `INGRESS_STUB_ENABLED` / `INGRESS_STUB_API_KEY` | stub (dev) |
| `INGRESS_YANDEX_EDA_ENABLED` / `INGRESS_YANDEX_EDA_API_KEY` | Яндекс Еда |
| `INGRESS_CHIBBIS_ENABLED` / `INGRESS_CHIBBIS_API_KEY` | Чиббис |
| `INGRESS_KUPER_ENABLED` / `INGRESS_KUPER_API_KEY` | Купер |

По умолчанию реальные партнёры **disabled**; `stub` enabled для разработки.

## Provider

`config/app.php` → `AggregatorIngressServiceProvider::class`.

## SKU bindings (операционно)

Данные в `ING_partner_sku_bindings` — пока через SQL/seed. UI в Filament — техдолг.

Пример:

```sql
INSERT INTO ING_partner_sku_bindings (partner_code, partner_sku, product_id, created_at, updated_at)
VALUES ('yandex-eda', 'YE-MENU-001', 1, NOW(), NOW());
```
