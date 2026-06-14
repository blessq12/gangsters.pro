# MarketingContent — роутинг

## Публичное API

| Метод | Путь | Назначение |
|-------|------|------------|
| GET | `/api/marketing` | Баннеры + акции |
| GET | `/api/marketing/banners` | Только баннеры |
| GET | `/api/marketing/promotions` | Только акции |

Регистрация: `routes/api.php`, группа `api`.

## Админка (Filament)

| Путь | Назначение |
|------|------------|
| `/admin/marketing` | Хаб (табы баннеры / акции) |
| `/admin/marketing/banners/create` | Новый баннер |
| `/admin/marketing/banners/{id}/edit` | Редактирование баннера |
| `/admin/marketing/promotions/create` | Новая акция |
| `/admin/marketing/promotions/{id}/edit` | Редактирование акции |
