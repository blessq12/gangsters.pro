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
- Admin: `app/Filament/Operations/`
- Frontend shopping: `resources/js/features/shoppingSession/` + `resources/js/processes/shoppingSession/`
