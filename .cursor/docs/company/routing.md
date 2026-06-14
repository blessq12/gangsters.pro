# Company — роутинг

## Публичное API

| Метод | Путь | Назначение |
|-------|------|------------|
| GET | `/api/company/main` | Профиль, контакты, расписание |
| GET | `/api/company/legals` | Юрлицо и реквизиты |
| GET | `/api/company/documents` | Legal-документы |

Регистрация: `routes/api.php`, группа `api`.

## Админка (Filament)

| Путь | Назначение |
|------|------------|
| `/admin/company` | Singleton-редактирование (5 табов) |

Панель: `admin`, slug ресурса — `company`.

## Чего нет

- Публичных write-endpoint'ов.
- REST CRUD — только одна страница редактирования.
