# Gangsters — guidance for agents

Модульные правила архитектуры и стиля проекта лежат в [`.cursor/rules/`](.cursor/rules/).

## С чего начать

1. **[`00-index.mdc`](.cursor/rules/00-index.mdc)** — всегда в контексте Cursor: карта слоёв, decision tree, глобальные MUST.
2. **Scoped rules** — подключаются по glob, когда редактируешь файлы слоя (см. таблицу в index).

## Карта rules

| Слой | Файл |
|------|------|
| Domain | [`backend-domain.mdc`](.cursor/rules/backend-domain.mdc) |
| Application | [`backend-application.mdc`](.cursor/rules/backend-application.mdc) |
| Infrastructure | [`backend-infrastructure.mdc`](.cursor/rules/backend-infrastructure.mdc) |
| HTTP (API) | [`backend-http.mdc`](.cursor/rules/backend-http.mdc) |
| Filament (admin) | [`backend-filament.mdc`](.cursor/rules/backend-filament.mdc) |
| Composition (Providers, Support, …) | [`backend-composition.mdc`](.cursor/rules/backend-composition.mdc) |
| Frontend application | [`frontend-application.mdc`](.cursor/rules/frontend-application.mdc) |
| Frontend presentation | [`frontend-presentation.mdc`](.cursor/rules/frontend-presentation.mdc) |
| Testing | [`testing-architecture.mdc`](.cursor/rules/testing-architecture.mdc) |

## Документация (дополнительно)

- [`.cursor/docs/README.md`](.cursor/docs/README.md) — индекс bounded contexts
- [`docs/aggregator-ingress.md`](docs/aggregator-ingress.md) — приём заказов от агрегаторов (кратко)
- [`docs/order-accounting-export.md`](docs/order-accounting-export.md) — экспорт заказов в системы учёта (кратко)
- [`.cursor/docs/aggregator-ingress/overview.md`](.cursor/docs/aggregator-ingress/overview.md) — BC AggregatorIngress (полностью)
- [`.cursor/docs/order-accounting-export/overview.md`](.cursor/docs/order-accounting-export/overview.md) — BC OrderAccountingExport (полностью)
- [`docs/architecture/bounded-context-dependency-matrix.md`](docs/architecture/bounded-context-dependency-matrix.md) — границы bounded contexts
- [`docs/admin-filament-hubs.md`](docs/admin-filament-hubs.md) — 5 admin hubs
- [`docs/shopping-session-csrf.md`](docs/shopping-session-csrf.md) — shopping cookie / CSRF

## Проверка границ

```bash
php artisan test --filter ArchitectureBoundaries
php artisan test --filter FrontendAndAclBoundaries
```

## Эталоны кода

- Backend: `app/Domain/Order/` → `app/Application/Order/` → `app/Infrastructure/Order/`
- Ingress агрегаторов: `app/Domain/AggregatorIngress/` → `app/Application/AggregatorIngress/` → `app/Infrastructure/AggregatorIngress/`
- Экспорт в учётку: `app/Domain/OrderAccountingExport/` → `app/Application/OrderAccountingExport/` → `app/Infrastructure/OrderAccountingExport/`
- Admin: `app/Filament/Operations/`
- Frontend shopping: `resources/js/features/shoppingSession/` + `resources/js/processes/shoppingSession/`
