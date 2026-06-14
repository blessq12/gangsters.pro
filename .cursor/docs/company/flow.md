# Company — флоу

## 1. Витрина читает профиль

```
SPA → GET /api/company/main
    → CompanyController
    → GetCompanyDataUseCase
    → CompanyRepository
    → CMP_company + Mapper
    → JSON { data: { name, phone, work_schedule, ... } }
```

## 2. Витрина читает реквизиты

```
GET /api/company/legals → GetCompanyLegalDataUseCase → CMP_company_legal
```

## 3. Футер читает документы

```
GET /api/company/documents → GetCompanyDocumentsUseCase → CMP_company_documents
```

## 4. Оператор редактирует

```
/admin/company → ManageCompany → save → CMP_company + legal + documents
```

## Разделение read / write

| Контур | Read | Write |
|--------|------|-------|
| Публичный API | 3 use case → Domain ports | — |
| Filament | Eloquent в форме | Eloquent save |

## Фронт (факт)

`systemStore` пока бьёт в `/api/system/company` — не подключён к `/api/company/main`.
