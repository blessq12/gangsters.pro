# Storefront — Application

Composition layer без собственного домена.

## Use cases

| Класс | Назначение |
|-------|------------|
| `GetStorefrontBootstrapUseCase` | Оркестрация read snapshot витрины |

### Зависимости `GetStorefrontBootstrapUseCase`

| Use case | BC |
|----------|-----|
| `GetCatalogUseCase` | Catalog |
| `GetDeliveryDataUseCase` | Delivery |
| `GetPromotionPolicyUseCase` | Promotion |
| `GetCompanyDataUseCase` | Company |
| `GetCompanyLegalDataUseCase` | Company |
| `GetCompanyDocumentsUseCase` | Company |
| `GetMarketingContentUseCase` | MarketingContent |

## События

Нет. Bootstrap — чистый read.
