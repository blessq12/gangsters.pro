# Company — слой домена

Ядро BC: сущности, value objects и контракты доступа к данным. Без Laravel, без HTTP, без Filament.

## Сущности

| Элемент | Смысл |
|---------|--------|
| `Company` | Публичный профиль: имя, бренд, описание, контакт, расписание, соцсети, logo |
| `CompanyLegalInfo` | Юридическая информация и банковские реквизиты |
| `CompanyDocument` | Документ по ключу (`privacy_policy`, `terms_of_use`, `user_agreement`) |

## Value objects

| VO | Смысл |
|----|--------|
| `CompanyContact` | phone, phoneAdditional, supportPhone, whatsappPhone, emailAddress, publicEmail |
| `CompanySchedule` | workHours, workSchedule[] |
| `WorkScheduleRow` | day (`mon`…`sun`), work, isDayOff |

## Перечисления

В домене Company **нет** enum-классов.

## Репозитории (порты)

| Порт | Ответственность |
|------|-----------------|
| `CompanyRepository` | Публичный профиль (`findPublic`) |
| `CompanyLegalRepository` | Юрлицо (`findPublic`) |
| `CompanyDocumentRepository` | Документы (`findAllOrdered`) |

Константа `CompanyRepository::SINGLETON_ID = 1`.

Домен **не знает** про таблицы `CMP_*` и Eloquent.

## Инварианты (семантика)

- Одна компания на инстанс (singleton).
- `work_schedule` — только дни `mon`…`sun`; невалидные строки отбрасываются маппером.
- Документы идентифицируются стабильным `key`, не slug.
