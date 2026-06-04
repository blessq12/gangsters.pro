# Admin Filament — 5 hubs

Admin UI = only the 5 hubs registered in `App\Providers\Filament\AdminPanelProvider`.

## TO-BE: Read / Write

| Операция | Разрешено | Запрещено |
|----------|-----------|-----------|
| **Read** (списки, fill форм, select options, overview) | `App\Infrastructure\**\Model\*` через Eloquent; композиция в `app/Filament/Support/*Query.php` | `GetAdmin*Query`, `ListAdmin*Query`, Domain в UI, Infrastructure repositories из Filament |
| **Write** | `Application\**\Command\*UseCase` + DTO через `Filament*FormMapper` | `$record->save()`, mass update, Domain из Filament |
| **Ошибки write** | `ApiException` → `Notification` + `Halt` | — |
| **Auth** | `AuthorizesAdminHub`, `AdminActionVisibility`, Gates | Роли в Resource |

**Исключение:** hub **Analytics** — `BusinessMetricsReader` через `InteractsWithBusinessMetrics` (агрегаты, не CRUD).

## Hubs

| Hub | Slug | Namespace |
|-----|------|-----------|
| Analytics | `dashboard` | `App\Filament\Analytics\` |
| Company | `company` | `App\Filament\Company\` |
| Catalog | `catalog` | `App\Filament\Catalog\` |
| Marketing | `marketing` | `App\Filament\Marketing\` |
| Operations | `operations` | `App\Filament\Operations\` |

Resources: только `create` / `edit`, `shouldRegisterNavigation = false`.

## Hub → read → write

| Hub | Экран / Resource | Read (Support / Model) | Write (UseCase) |
|-----|------------------|------------------------|-----------------|
| Analytics | Dashboard widgets | `BusinessMetricsReader` | — |
| Operations | `HubOrdersTable` | `AdminOrderTableQuery` | `CreateAdminOrderUseCase`, `UpdateAdminOrderUseCase`, status/paid/cancel actions |
| Operations | `HubClientsTable` / Edit client | `AdminClientTableQuery`, `AdminClientEditReadHelper` | `UpdateAdminClientUseCase`, block/unblock, address, password reset |
| Operations | `HubActiveCartsTable` | `AdminActiveCartsTableQuery`, `AdminActiveCartSnapshotBuilder` | read-only modal |
| Operations | `HubNotificationsTable` | `AdminNotificationDeliveryTableQuery` | read-only |
| Operations | `HubCartRulesProductsTable` | `AdminCartRuleProductsTableQuery` | `UpdateProductCartRuleFlagsUseCase` |
| Operations | `HubCartRulesPanel` | `AdminCartRuleSettingsReadHelper` | `UpdateCartRuleSettingsUseCase` |
| Operations | `HubDeliveryZonePanel` | `AdminCompanyReadHelper` | `UpdateAdminDeliverySettingsUseCase` |
| Catalog | `HubProductsTable` / Edit product | `AdminProductTableQuery`, `PRD_Product` | `UpdateProductUseCase`, activate/archive, images, tags |
| Catalog | `HubTagsTable` / Edit tag | `PRD_Tag` | tag Command UseCases |
| Catalog | `HubCategoriesTable` / Edit category | `PRD_Category` | `CreateCategoryUseCase`, `UpdateCategoryUseCase`, activate/deactivate |
| Catalog | `HubLayoutTable` | `AdminCatalogLayoutReadHelper` | `SetCategoryProductsUseCase` |
| Catalog | `CatalogOverviewWidget` | `AdminCatalogOverviewReadHelper` | — |
| Marketing | `HubBannersTable` / Edit banner | `SYS_Banner`, `ResolvesAdminBannerImageUrl` | `SaveBannerUseCase`, `DeleteBannerUseCase` |
| Marketing | `HubPromotionsTable` / Edit promotion | `SYS_Promotion` | `SavePromotionUseCase`, `DeletePromotionUseCase` |
| Company | `HubDocumentsTable` | `SYS_Document` | document Command UseCases |
| Company | `HubStaffTable` | `User` | staff Command UseCases |
| Company | `HubCompanyProfilePanel` | `AdminCompanyReadHelper` | `UpdateAdminCompanyProfileUseCase` |
| Company | `HubCompanyLegalPanel` | `AdminCompanyReadHelper` | `UpdateAdminCompanyLegalUseCase` |
| Company | `HubCompanySeoPanel` | `AdminSiteSeoReadHelper` | `UpdateAdminSiteSeoSettingsUseCase` |
| Operations | Order create/edit forms | `AdminProductSearchQuery` | см. order UseCases выше |

Простые hub-списки без Support-класса: прямой Eloquent `Model::query()` в `Hub*Table`.

**Application `GetAdmin*Query` (2026-06):** hub UI не использует; остались только internal read для UseCase (return-after-write, settings snapshot, category layout). Список: `GetAdminOrderDetailQuery`, `GetAdminCategoryDetailQuery`, `GetAdminCategoryLayoutQuery`, `GetAdminCompanyProfileQuery`, `GetAdminCompanyLegalQuery`, `GetAdminSiteSeoSettingsQuery`, `GetAdminDeliverySettingsQuery`, `GetAdminCartRuleSettingsQuery`.

## Anti-patterns

- Бизнес-правила в Filament (статусы заказа, cart rules) — только UseCase/Domain
- `$record->save()` на доменных сущностях при submit
- `GetAdmin*Query` в `app/Filament/**` (кроме Analytics)
- Смешение Query и Eloquent на одном экране после миграции
- Analytics на `ORD_Order::query()` без отдельного решения

## Regression (write — без изменений)

Проверять после PR: `AdminRoleAccessTest`, hub access, pagination render, create order/client, catalog/marketing/company delete actions, settings save.

