# Client — события

## Доменные события

| Событие | Когда | Класс |
|---------|--------|-------|
| `ClientRegistered` | После save + `markRegistered()` | `App\Domain\Client\Event\ClientRegistered` |
| `ClientUnauthorizedAccessDetected` | 401 на `api/client/*` | `App\Domain\Client\Event\ClientUnauthorizedAccessDetected` |

`ClientRegistered` dispatch в `RegisterClientUseCase` через `Event::facade`.

Plain PHP objects — без Laravel contracts.

## Подписчики

| Listener | Событие | Состояние |
|----------|---------|-----------|
| — | `ClientRegistered` | **Нет** подписчиков |
| Handler (dispatch only) | `ClientUnauthorizedAccessDetected` | Логирование/интеграция не подключена |

## События SPA (не BC Client)

| Событие | Когда |
|---------|--------|
| `CLIENT_LOGGED_IN` | register / login |
| `CLIENT_LOGGED_OUT` | clearAuth / clear |
| `CLIENT_PROFILE_CHANGED` | fetch / update profile |
| `CLIENT_ADDRESS_SELECTED` | selectAddress |
| `CLIENT_ADDRESS_CREATED` | addClientAddress |
| `CLIENT_ADDRESS_DELETED` | deleteClientAddress |

Определены в `resources/js/shared/domainEvents.js`.

## Чего нет

- `ClientProfileUpdated`, `ClientAddressAdded` на беке.
- Outbox / async queue.
- Eloquent model events на `CLN_*`.
- Webhooks / CRM sync.

## Целевые интеграции (не реализовано)

```
ClientRegistered
  → welcome email / CRM / analytics

Checkout SetCheckoutClient
  → ClientLookupPort::assertExists(clientId)
```
