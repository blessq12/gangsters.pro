# MarketingContent — слой домена

Ядро BC: сущности, порты репозиториев и медиа. Без Laravel, без HTTP, без Filament.

## Сущности

| Элемент | Поля | Методы |
|---------|------|--------|
| `Banner` | `id`, `title`, `description`, `imageDesktop`, `imageMobile`, `sortOrder`, `isActive` | геттеры по каждому полю |
| `Promotion` | `id`, `title`, `body`, `image`, `sortOrder`, `isActive` | геттеры по каждому полю |

Файлы:
- `app/Domain/MarketingContent/Entity/Banner.php`
- `app/Domain/MarketingContent/Entity/Promotion.php`

Обе сущности — `final class`, поля `private readonly`.

## Порты

### Репозитории

| Порт | Метод | Ответственность |
|------|-------|-----------------|
| `BannerRepository` | `findActiveOrdered(): list<Banner>` | Активные баннеры, упорядоченные |
| `PromotionRepository` | `findActiveOrdered(): list<Promotion>` | Активные акции, упорядоченные |

Файлы:
- `app/Domain/MarketingContent/Repository/BannerRepository.php`
- `app/Domain/MarketingContent/Repository/PromotionRepository.php`

### Медиа

| Порт | Метод | Ответственность |
|------|-------|-----------------|
| `MarketingMediaUrlPort` | `resolve(?string $path): ?string` | Storage path → публичный URL для API |

Файл: `app/Domain/MarketingContent/Port/MarketingMediaUrlPort.php`.

## Перечисления

В домене MarketingContent **нет** enum-классов.

## Инварианты (семантика)

- Публичный read отдаёт только записи с `is_active = true`.
- Порядок — `sort_order`, при равенстве — `id` (задаётся в Infrastructure repository).
- Пути изображений в домене — строки как в БД; резолв URL — через порт, не в entity.
- `body` акции — HTML; plain-text excerpt для карточки — в Application presenter, не в домене.

Домен **не знает** про `MKT_*`, Eloquent, `Storage` и Filament.

## Чего нет в домене

- Команд записи, событий, валидации upload.
- Связи с каталогом, checkout, BC Promotion.
