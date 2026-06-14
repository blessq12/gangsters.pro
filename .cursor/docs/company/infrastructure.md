# Company — слой инфраструктуры

Реализация доменных портов и персистентность. Eloquent и таблицы `CMP_*`.

## Модели (`CMP_*`)

| Модель | Таблица | Смысл |
|--------|---------|--------|
| `CMP_Company` | `CMP_company` | Профиль, контакты, расписание, соцсети |
| `CMP_CompanyLegal` | `CMP_company_legal` | Реквизиты (FK → company) |
| `CMP_CompanyDocument` | `CMP_company_documents` | Документы по `key` (FK → company) |

## Репозитории

| Класс | Порт |
|-------|------|
| `EloquentCompanyRepository` | `CompanyRepository` |
| `EloquentCompanyLegalRepository` | `CompanyLegalRepository` |
| `EloquentCompanyDocumentRepository` | `CompanyDocumentRepository` |

Маппинг — `CompanyMapper`, `CompanyLegalMapper`, `CompanyDocumentMapper`.

## Композиция

`CompanyServiceProvider` регистрирует привязки портов (`config/app.php`).

## Сидирование

`Database\Seeders\CompanySeeder` — демо-профиль, реквизиты, 3 документа. Вызывается из `DatabaseSeeder`.
