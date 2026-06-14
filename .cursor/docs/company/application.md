# Company — слой приложения

Оркестрация read-сценариев: читает домен через порты, собирает JSON-контракты.

## Активные сценарии

| Сценарий | Назначение |
|----------|------------|
| `GetCompanyDataUseCase` | Публичный профиль (`/api/company/main`) |
| `GetCompanyLegalDataUseCase` | Юрлицо и реквизиты (`/api/company/legals`) |
| `GetCompanyDocumentsUseCase` | Legal-документы (`/api/company/documents`) |

Расположение: `app/Application/Company/useCases/`.

## Что делает `GetCompanyDataUseCase`

1. Загружает `Company` через `CompanyRepository::findPublic()`.
2. Если строки нет — `{ data: null }`.
3. Иначе плоский JSON: профиль, контакты, `work_hours`, `work_schedule[]`, соцсети.

## Админские мутации

Сейчас **не** проходят через Application Company. Filament пишет в `CMP_*` через Eloquent (см. `filament.md`).

## DTO / Presenter

Отдельных DTO и Presenter **нет** — маппинг в use case, как в Catalog/Delivery.
