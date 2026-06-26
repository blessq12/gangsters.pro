# MarketingContent — потоки

Сквозные сценарии BC и связанные контуры.

## 1. Витрина: read API

```
SPA → GET /api/marketing/banners | /promotions
    → MarketingContentController
    → GetMarketingContentUseCase
    → BannerRepository / PromotionRepository
    → Eloquent MKT_* + Mapper
    → MarketingContentPresenter (+ MarketingMediaUrlPort)
    → JSON { data: [...] }
```

Combined endpoint `GET /api/marketing` отдаёт `{ data: { banners, promotions } }` — в SPA не используется.

Фильтр: только `is_active = true`, порядок `sort_order, id`.

## 2. Витрина: SPA read model

```
MainLayout* onMounted
    → marketingStore.fetchAll() (Promise.allSettled banners + promotions)

HomeJumbotron* / HomePromotions*
    → useMarketingReadModel({ autoload: true })
    → marketingStore.fetchAll() (повторно при mount компонента)
    → marketingMappers.normalize*
    → UI (карусель, карточки акций, modal с body HTML)
```

**Потребители:**

| Место | Данные |
|-------|--------|
| `HomeJumbotronBase` (+ Desktop/Mobile обёртки) | banners |
| `HomePromotionsDesktop` / `HomePromotionsMobile` | promotions |
| `HomePageDesktop` / `HomePageMobile` | оба блока |
| `MainLayoutDesktop` / `MainLayoutMobile` | prefetch |

Нормализация на фронте отбрасывает записи без изображений desktop/mobile.

## 3. Оператор: hub

```
Браузер → /admin/marketing?tab=banners|promotions
    → ManageMarketingContent
    → BannersHubTable | PromotionsHubTable
    → create / edit / delete / reorder
```

## 4. Оператор: create/edit

```
Hub → CreateBanner | EditBanner | CreatePromotion | EditPromotion
    → BannerForm | PromotionForm
    → FileUpload → public disk (marketing/banners/*, marketing/promotions/*)
    → Eloquent save MKT_*
    → RedirectsToMarketingHub → /admin/marketing?tab=...
```

При edit без новой загрузки: `PreservesMarketingMediaOnEmptyUpload` сохраняет старый path.

При смене/удалении image: `MarketingStoredMedia` удаляет старый файл с disk (кроме `/images/*` и http URL).

## 5. Следующий read

Изменения видны при следующем `GET /api/marketing/*` (общая БД, кэша на read нет).

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | `GetMarketingContentUseCase` → Domain ports | — |
| Filament | Eloquent в hub/forms | Eloquent save / delete / reorder |

Write use cases в Application **не** внедрены.

## Не входит в BC

- Checkout benefits, подарки — BC Promotion.
- Каталог, корзина — другие контексты.
