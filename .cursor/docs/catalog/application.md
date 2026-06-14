# Catalog — слой приложения

Оркестрация публичного read-сценария: читает домен через порты, собирает JSON для витрины.

## Активные сценарии

| Сценарий | Назначение |
|----------|------------|
| `GetCatalogUseCase` | Публичное дерево каталога для SPA |

Файл: `app/Application/Catalog/useCases/GetCatalogUseCase.php`.

Presenter/DTO **нет** — маппинг в приватных методах use case.

## Поведение `GetCatalogUseCase`

1. `TagRepository::findAllActiveOrdered()` → индекс по id.
2. Для каждой категории из `CategoryRepository::findAllOrdered()`:
   - `findItemsByCategoryId` → links;
   - batch-load активных products/sets по ids;
   - resolve items в порядке links;
   - категория без items **пропускается**.
3. Возврат `{ categories: [...] }`.

Зависимости — только `CategoryRepository`, `CatalogItemRepository`, `TagRepository`.

## JSON-контракт `GET /api/catalog`

Корень ответа (без обёртки `data`):

```json
{
  "categories": [
    {
      "category": {
        "id": 1,
        "name": "Роллы",
        "slug": "rolls",
        "sort_order": 0,
        "is_active": true
      },
      "items": [ /* product | set */ ]
    }
  ]
}
```

### Product item

| Поле | Тип |
|------|-----|
| `kind` | `"product"` |
| `id`, `name`, `slug`, `status` | |
| `price` | `{ amount: int, currency: string }` — рубли |
| `description` | `?string` |
| `nutrition` | `?{ calories, proteins, fats, carbs, basis }` |
| `ingredients` | `list<string>` |
| `tags` | `list<{ code, label, color }>` |

### Set item

| Поле | Тип |
|------|-----|
| `kind` | `"set"` |
| `id`, `name`, `slug`, `status`, `price`, `description` | |
| `lines` | `list<{ product_id, quantity }>` |
| `tags` | `list<{ code, label, color }>` |

### Не включается в API

- `images` (хранятся в `PRD_product_images`, Filament relation manager).
- Meta: `meta_counts_as_roll`, `meta_gift_candidate`, `meta_is_complement_set`.

## Админские мутации

**Не** проходят через Application Catalog. Filament пишет в `PRD_*` напрямую (см. `filament.md`).

## Интеграция с Checkout (вне Application Catalog)

Checkout ACL-адаптеры в `app/Infrastructure/Checkout/Port/` читают Catalog:

| Порт | Адаптер | Источник |
|------|---------|----------|
| `CatalogPricingPort` | `CatalogPricingAdapter` | `CatalogItemRepository::findProductById` (только товары) |
| `CatalogGiftCandidatesPort` | `CatalogGiftCandidatesAdapter` | `PRD_Product` where `meta_gift_candidate=true` |
| `CatalogRollMetaPort` | `CatalogRollMetaAdapter` | `PRD_Product.meta_counts_as_roll` |
| `CatalogComplementSetCandidatesPort` | `CatalogComplementSetCandidatesAdapter` | `PRD_Product` where `meta_is_complement_set=true` |

Биндинги — `CheckoutServiceProvider`, не `CatalogServiceProvider`.
