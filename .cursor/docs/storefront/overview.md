# Storefront — обзор (composition root)

**Роль:** единая точка загрузки данных витрины для SPA. **Не bounded context** — нет домена и персистентности; оркестрация read use case'ов других BC.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Bootstrap** | Один HTTP-ответ: catalog, delivery, promotion, company, marketing + `version` |
| **Version** | ISO timestamp для invalidation кэша на клиенте |

## Границы

| Внутри | Снаружи |
|--------|---------|
| `GetStorefrontBootstrapUseCase` | Catalog, Delivery, Promotion, Company, MarketingContent BC |
| `StorefrontController` | OrderDraft / PlaceOrder (отдельные endpoint'ы) |

## Пути в коде

| Слой | Путь |
|------|------|
| Application | `app/Application/Storefront/useCases/GetStorefrontBootstrapUseCase.php` |
| HTTP | `app/Http/Controllers/Api/StorefrontController.php` |
| SPA | `resources/js/stores/storefrontStore.js` |

## SPA

При старте приложения (`MainLayoutMobile` / `MainLayoutDesktop`):

```
storefrontStore.fetchBootstrap()
  → GET /api/storefront/bootstrap
  → catalogStore.applyBootstrapCatalog
  → deliveryStore.data
  → companyStore.applyBootstrap
  → marketingStore.applyBootstrap
```

Legacy отдельные вызовы `GET /catalog`, `/delivery`, … остаются для совместимости, но витрина использует bootstrap.

## См. также

- [http.md](http.md) — контракт API
- [flow.md](flow.md) — диаграмма загрузки
- [Order OrderDraft](../order/application.md#orderdraft-сайт) — оформление и создание заказа
