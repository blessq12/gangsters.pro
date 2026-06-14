# MarketingContent — Filament

Хаб `/admin/marketing` с табами **Баннеры** и **Акции**. Мутации **мимо** Application — прямо в `MKT_*` через Eloquent.

## Точка входа

- **Страница:** `ManageMarketingContent` → `/admin/marketing`
- Пункт навигации: **Маркетинг** (`navigationSort` = 15, иконка `OutlinedMegaphone`)
- Табы: `?tab=banners` / `?tab=promotions` (Livewire widgets)

## Ресурсы

| Ресурс | Model | Slug | Навигация |
|--------|-------|------|-----------|
| `MarketingContentResource` | `MKT_Banner` (form/table пустые) | `marketing` | Видим (хаб) |
| `BannerResource` | `MKT_Banner` | `marketing/banners` | Скрыта |
| `PromotionResource` | `MKT_Promotion` | `marketing/promotions` | Скрыта |

Регистрация: `AdminPanelProvider::resources()` + Livewire widgets `BannersHubTable`, `PromotionsHubTable`.

## Hub tables

| Widget | Операции |
|--------|----------|
| `BannersHubTable` | list, reorder `sort_order`, create/edit/delete |
| `PromotionsHubTable` | то же |

Колонки: `title` (searchable), badge `is_active` (`MarketingHubTablePresentation`).  
Actions: create → resource create URL; edit; delete (`MarketingHubTableActions`).  
Default sort: `sort_order`.

## Формы

### `BannerForm`

| Поле | Колонка | UI |
|------|---------|-----|
| Заголовок | `title` | required, max 255 |
| Описание | `description` | textarea |
| Изображение desktop | `image_desktop` | FileUpload |
| Изображение mobile | `image_mobile` | FileUpload |
| Активен | `is_active` | toggle, default true |

`sort_order` в форме **нет**.

### `PromotionForm`

| Поле | Колонка | UI |
|------|---------|-----|
| Заголовок | `title` | required, max 255 |
| Изображение | `image` | FileUpload |
| Текст акции | `body` | textarea HTML (helperText про HTML) |
| Активна | `is_active` | toggle, default true |

## Upload (`MarketingMediaUpload`)

| Параметр | Значение |
|----------|----------|
| Disk | `public`, visibility `public` |
| MIME | `image/jpeg`, `image/png`, `image/webp` |
| Desktop | `marketing/banners/desktop` |
| Mobile | `marketing/banners/mobile` |
| Promotion | `marketing/promotions` |
| maxSize | `config/marketing.{banner\|promotion}.max_upload_kb` если `> 0` |

## Страницы create/edit

| Страница | После save |
|----------|------------|
| `CreateBanner`, `EditBanner` | редирект на хаб `?tab=banners` |
| `CreatePromotion`, `EditPromotion` | редирект на хаб `?tab=promotions` |

`EditBanner` / `EditPromotion` используют `PreservesMarketingMediaOnEmptyUpload` — при пустом FileUpload не затирают существующий path (важно для seeder-путей `/images/*`, которые Filament не показывает).

## Concerns / Support

| Класс | Роль |
|-------|------|
| `RedirectsToMarketingHub` | Редирект после create/edit на хаб с tab |
| `HasMarketingHubIndexUrl` | `getIndexUrl()` → хаб с tab |
| `PreservesMarketingMediaOnEmptyUpload` | Сохранение media path при пустом upload |
| `MarketingMediaUpload` | FileUpload-поля |
| `MarketingHubTableActions` | CRUD actions в hub |
| `MarketingHubTablePresentation` | Колонка статуса |

## Паттерны UI

- Скрытые ресурсы доступны только через hub table и прямые URL create/edit.
- Reorder в hub table меняет `sort_order` напрямую в БД.
- Новая запись без явного `sort_order` получает `max + 1` через Eloquent hook.
