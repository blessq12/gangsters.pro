# Client applications (option A)

Two independent SPA/PWA clients against one Laravel core. No shared frontend code between them.

| App | Sources | Vite | Build output | PWA | HTTP | API contract |
|---|---|---|---|---|---|---|
| Public | `apps/public` | `apps/public/vite.config.js` | `public/build/public` | `public/pwa/public` | `apps/public/http/` | `/api/public/*` |
| Backoffice | `apps/backoffice` (shell skeleton) | `apps/backoffice/vite.config.js` | `public/build/backoffice` | `public/pwa/backoffice` | `apps/backoffice/http/` | `/api/backoffice/*` (auth + ping) |

Rules:
- no imports between `apps/public` and `apps/backoffice`
- no shared HTTP/API client; each app owns its `http/transport` and `api/*`

Scripts from repo root:

- `npm run dev:public` / `npm run build:public`
- `npm run dev:backoffice` / `npm run build:backoffice`
- `npm run build` — both builds + PWA sync
