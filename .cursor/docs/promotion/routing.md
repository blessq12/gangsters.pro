# Promotion — маршрутизация

## API (целевое, опционально)

| Метод | Путь | Controller | Use case |
|-------|------|------------|----------|
| GET | `/api/promotion` | `PromotionController` | `GetPromotionPolicyUseCase` |

Регистрация в `routes/api.php` — при внедрении read API.

## Filament (целевое)

| Путь | Resource / Page |
|------|-----------------|
| `/admin/promotion` | `ManagePromotion` (singleton settings) |

Hub в навигации Operations рядом с Delivery.
