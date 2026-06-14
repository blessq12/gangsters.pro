# Company — обзор BC

**Роль:** публичная и юридическая идентичность бренда — профиль, контакты, расписание, реквизиты, legal-документы.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Компания** | Singleton-профиль бренда (`Company`, id=1) |
| **Контакты** | Телефоны, email, мессенджеры (`CompanyContact`) |
| **Расписание** | Краткая строка + расписание по дням (`CompanySchedule`) |
| **Юрлицо** | Реквизиты и compliance-поля (`CompanyLegalInfo`) |
| **Документ** | Публичный legal-текст по фиксированному `key` (`CompanyDocument`) |

## Границы

- **Внутри BC:** имя, описание, контакты, соцсети, режим работы, реквизиты, документы футера.
- **Снаружи:** доставка, корзина, заказ, оплата — другие контексты.
- Публичный контракт — **только чтение** (`GET /api/company/*`).
- Запись — **только Filament** (`/admin/company`).
- Адрес кухни, тарифы и зона доставки — **Delivery BC**, не Company.
- SPA пока читает `/api/system/*` — артефакт; целевые endpoint'ы — `/api/company/*`.

## Хранение

Таблицы `CMP_*`: `CMP_company`, `CMP_company_legal`, `CMP_company_documents` — singleton (company id=1).

## Пути в коде

| Слой | Компания |
|------|----------|
| Domain | `app/Domain/Company/` |
| Application | `app/Application/Company/` |
| Infrastructure | `app/Infrastructure/Company/` |
| HTTP | `app/Http/Controllers/Api/CompanyController.php` |
| Filament | `app/Filament/Company/` |
