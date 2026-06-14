# Company — Filament (оператор)

UI оператора для мутаций компании. Пишет в `CMP_*` через Eloquent, минуя Application.

## Точка входа

- **Страница:** `ManageCompany` → `/admin/company`
- Навигация: **Компания** (`navigationSort` = 30)
- Табы: Профиль / Контакты / Расписание / Юрлицо / Документы

## Ресурсы

| Resource | Модель | Операции |
|----------|--------|----------|
| `CompanyResource` | `CMP_Company` | index (singleton EditRecord) |

При первом заходе: `firstOrCreate` company id=1, legal и 3 документа.

## Сохранение связанных данных

- Основная форма — `CMP_Company`.
- Поля `legal_*` → `CMP_CompanyLegal` в `afterSave`.
- Поля `document_{key}_title/content` → `CMP_CompanyDocument` в `afterSave`.

## Расписание

`Repeater` `work_schedule`: 7 фиксированных строк (mon…sun), без add/delete.

## Документы

Фиксированные ключи: `privacy_policy`, `terms_of_use`, `user_agreement`.

## Регистрация

`AdminPanelProvider::resources()` — `CompanyResource::class`.
