# Catalog — флоу

Сквозные сценарии BC и связанные контуры.

## 1. Витрина: read API

```
SPA → GET /api/catalog
    → CatalogController
    → GetCatalogUseCase
    → CategoryRepository + CatalogItemRepository + TagRepository
    → Eloquent PRD_* + Mappers
    → JSON { categories: [ { category, items[] } ] }
```

Только **активные** категории и позиции. Категории без items **не попадают** в ответ.  
**Images и meta** в JSON **нет**.

## 2. Витрина: SPA read model

```
MainLayout* / useAppBootstrap
    → catalogStore.initFromStorage() / fetchAll()
    → catalogService.fetchCatalogTree()
        → items[] маппится в products[] на фронте
    → HomePage (useCatalogPageModel)
    → CatalogCategories*, CatalogProducts*, ProductCard*, ProductDetailModal*
```

Отдельного route `/catalog` **нет** — каталог на главной.

`catalogStore` кэширует дерево в localStorage (`gangsters_catalog`).

Клиентские фильтры: категория, тег, поиск по name, `desktopCardsPerRow`, `mobileCardViewMode`.

### Разрыв BE/FE по изображениям

`catalogMappers.js` ожидает `images[].variants[]` (thumb/medium/large).  
API не отдаёт `images` → `imageUrl` / `imageSrcset` на витрине пустые.

## 3. Корзина с карточки

```
useProductActions → useCartCommands
    → DOMAIN_EVENTS.CART_* (source: "catalog")
```

Цена в корзине — через Checkout API, не напрямую из catalogStore.

## 4. Checkout ACL (косвенно)

```
UpdateCheckoutCartUseCase → CatalogPricingPort → CatalogPricingAdapter
EvaluateCheckoutBenefits → CatalogGiftCandidatesPort, CatalogComplementSetCandidatesPort
CartRollCounter → CatalogRollMetaPort
```

Gift/complement adapters читают `PRD_Product` напрямую (meta-поля), минуя domain repos.

## 5. Оператор: hub

```
Браузер → /admin/catalog?tab=products
    → ManageCatalog
    → активный *HubTable (одна таблица в DOM)
```

## 6. Оператор: товар

```
/admin/catalog/products/create → catalog_kind=product
/admin/catalog/products/{id}/edit
    → табы card / nutrition / meta / images
    → save → FilamentProductPersistence::normalize
    → Eloquent PRD_Product + tags sync
    → RedirectsToCatalogHub
```

Изображения — `ProductImagesRelationManager`, path `products/{id}/` на `public` disk.

## 7. Оператор: категория и набор

```
Edit категории → composition → CategoryProductsRelationManager → PRD_category_product
Edit набора → composition → ProductSetLinesRelationManager → PRD_product_set_lines
```

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | `GetCatalogUseCase` → Domain ports | — |
| Filament | Eloquent в hub/forms/RM | Eloquent save/delete/reorder |
| Checkout ACL | Adapters → repos / Eloquent | — |

Write use cases в Application **не** внедрены.

## Следующий read

Изменения в админке видны при следующем `GET /api/catalog` (общая БД; SPA может читать из localStorage до refresh).
