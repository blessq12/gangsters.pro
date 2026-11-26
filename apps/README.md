# Client applications

Two independent SPA/PWA clients against one Laravel core.

| App        | Sources           | Vite                             | Build                     | HTTP                    | API                               |
| ---------- | ----------------- | -------------------------------- | ------------------------- | ----------------------- | --------------------------------- |
| Public     | `apps/public`     | `apps/public/vite.config.js`     | `public/build/public`     | `apps/public/http/`     | `/api/public/*`                   |
| Backoffice | `apps/backoffice` | `apps/backoffice/vite.config.js` | `public/build/backoffice` | `apps/backoffice/http/` | `/api/backoffice/*` (auth + ping) |

## Backoffice: shell + role modules

- `shell/**` — auth, transport, guard, topbar layout, primitives, role registry
- `roles/{owner,administrator,production,courier}` — isolated modules (home, nav, routes, optional layout)
- Linking point: `shell/roles/registry.js` only (`kitchen` API role → `roles/production`)
- Adding a role: new folder under `roles/` + one registry line

Rules:

- no imports between role folders
- shell must not import `roles/*` except via registry
- role routes load lazily (dynamic import)
