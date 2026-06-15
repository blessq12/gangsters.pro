# Delivery — маршрутизация

| Method | URI | Controller | Middleware |
|--------|-----|------------|------------|
| GET | `/api/delivery` | `DeliveryController@show` | `api` |
| GET | `/api/storefront/bootstrap` | `StorefrontController@bootstrap` | `api` (блок delivery) |

Filament: `/admin/delivery`.

Provider: `DeliveryServiceProvider`.

## OrderDraft (Order BC)

| Method | URI |
|--------|-----|
| POST | `/api/order-drafts/preview` |
| POST | `/api/orders` |

Geocode — через `DeliveryAddressGeocoderPort`, не отдельный HTTP route.
