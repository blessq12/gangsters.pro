# Bounded Context Dependency Matrix

## Backend contexts

| Context | Can depend on | Cannot depend on |
| --- | --- | --- |
| `Catalog` | `shared` | `Client`, `Order`, `Reporting`, `Integrations` |
| `Client` | `shared` | `Catalog`, `Order`, `Reporting`, `Integrations` |
| `Order` | `shared`, domain ports | `Client` entities, `Catalog` repositories, `Application` contracts |
| `Promotions` | `shared`, `Catalog` read models | `Order` internals, `Client`, `Integrations` |
| `Reporting` | read models, integration events | domain writes, domain entities with behavior |
| `Integrations/YandexFood` | application contracts, presenters, ACL mappers | `OrderFactory`, `OrderItemsFactory`, domain repositories of foreign contexts |

## Frontend contexts

| Context | Can depend on | Cannot depend on |
| --- | --- | --- |
| `app` | processes, router, shell, shared | domain stores directly (только через фасады/features и процессы) |
| `domains/catalog` | shared, shopping-session commands, cart/domain events | `client` store internals, `order` store internals |
| `domains/client` | shared, client read models (`useClientReadModel`), client commands (`useClientCommands`), order read models (`useClientOrderSummaryReadModel`, `useClientOrderHistoryReadModel`) | `cart` store internals, `order` store internals |
| `domains/order` | shared, order read models, order commands (`useOrderCommands`), client read contracts | `client` storage model internals, raw cart store |
| `domains/shopping-session` | shared, cart read/command facades (`useCartReadModel`, `useCartCommands`), domain events | `client` and `order` store internals |
| `domains/system` | shared read models (`useSystemReadModel`) | client/order/cart internals |

## Event rules

- Domain events stay inside bounded context semantics: `OrderCreated`, `OrderUpdated`, `OrderCancelled`.
- Integration events cross context boundaries: `OrderCreatedIntegrationEvent`, `OrderUpdatedIntegrationEvent`, `OrderCancelledIntegrationEvent`.
- Frontend cross-domain reactions go through `domainEvents.js`, not direct store-to-store imports.
- Read models may subscribe to events, but they do not own business decisions.
