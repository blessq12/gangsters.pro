# Лимиты загрузки медиа в админке (Filament / Marketing)

Ошибка вида `validation.uploaded` или «Не удалось загрузить файл» при загрузке баннера **часто означает**, что файл отклонил **PHP или веб-сервер**, а не формат в Filament.

## Быстрая проверка

```bash
php -i | grep -E 'upload_max_filesize|post_max_size'
php artisan marketing:check-upload-limits
```

Рекомендуемые значения PHP:

| Параметр | Минимум для баннеров |
|----------|----------------------|
| `upload_max_filesize` | **64M** |
| `post_max_size` | **128M** (два файла + форма) |

Лимит приложения задаётся в `.env`: `MARKETING_BANNER_MAX_UPLOAD_KB` и `MARKETING_PROMOTION_MAX_UPLOAD_KB`.

- **`0` (по умолчанию)** — прикладного потолка нет; Filament/Livewire проверяют только «это файл», размер ограничивает **PHP** (`upload_max_filesize`).
- **> 0** — жёсткий cap в KB: `min(конфиг, PHP)` в форме и в Livewire `max:`.

## Mobile и desktop — разные файлы

Поля **Mobile** и **Desktop** настраиваются одинаково, но каждый файл уходит **отдельным** запросом Livewire. Лимит `upload_max_filesize` действует **на один файл**.

Если desktop загрузился, а mobile — нет с текстом про `upload_max_filesize`, чаще всего **mobile-файл тяжелее** (например >2 MB при PHP `upload_max_filesize=2M`), а desktop — легче. Это не «сломанное поле mobile», а размер конкретного файла.

При сохранении формы с двумя крупными файлами дополнительно смотрите `post_max_size` (сумма тел запроса).

## Где править

### Laravel Herd / Valet (macOS)

Настройки PHP в UI Herd или `php.ini` выбранной версии PHP. После изменения перезапустите PHP-FPM / Herd.

### Apache + PHP-FPM (`public/` как document root)

Скопируйте [`public/.user.ini.example`](../public/.user.ini.example) в `public/.user.ini` и перезапустите PHP-FPM.

### Docker / php-fpm

В образе или `php.ini` / `conf.d/uploads.ini`:

```ini
upload_max_filesize = 64M
post_max_size = 128M
```

### Nginx (перед PHP)

```nginx
client_max_body_size 128m;
```

## Связь с Filament

1. Livewire принимает файл на `/livewire/upload-file` (временное хранилище).
2. Filament сохраняет на disk `media` в `marketing/banners` или `marketing/promotions`.
3. При сохранении формы пути попадают в `SaveBannerUseCase` / `SavePromotionUseCase`.

Если шаг 1 падает — поднимайте **PHP/nginx**, затем при необходимости `MARKETING_*_MAX_UPLOAD_KB`.
