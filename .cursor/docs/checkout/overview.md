# Checkout — обзор BC

**Роль:** серверный объект **намерения оформления** — клиент пошагово заполняет блоки (корзина, клиент, доставка, оплата) и подтверждает. После подтверждения BC публикует доменное событие `CheckoutConfirmed` → Order BC создаёт заказ.

## Семантика

| Термин | Смысл |
|--------|--------|
| **Checkout (агрегат)** | Черновик оформления с UUID, статус `draft` \| `confirmed` |
| **Блок корзины** | Слепок позиций: товар, количество, цена на момент изменения, итог |
| **Блок клиента** | Guest (контакт) или Registered (`client_id` + опциональный слепок) |
| **Блок доставки** | Способ (`courier` \| `pickup`), адрес, комментарий, `scheduled_at` |
| **Блок оплаты** | Способ (`cash` \| `card_courier` \| `card_online`), сдача с (`change_from_rubles`) |
| **Подтверждение** | Перевод в `confirmed` + событие `CheckoutConfirmed` |

## Границы

| Внутри BC | Снаружи |
|-----------|---------|
| Агрегат, слепки блоков, lifecycle draft → confirmed | Создание **заказа** (Order BC) |
| Цена товара при upsert корзины — через `CatalogPricingPort` | Тарифы/зона доставки (Delivery BC) |
| Хранение черновика в `CHK_checkouts` | Профиль клиента, адресная книга (Client BC) |
| Публичный write-API `/api/checkout/*` | Legacy `/api/shopping/*` — **удалён** |

## Хранение

Таблица `CHK_checkouts` — одна строка на объект оформления. Блоки — JSON-колонки. Деньги в слепке корзины — **рубли** (`App\Shared\ValueObject\Money`).

## Пути в коде

| Слой | Checkout |
|------|----------|
| Domain | `app/Domain/Checkout/` |
| Application | `app/Application/Checkout/` |
| Infrastructure | `app/Infrastructure/Checkout/` |
| HTTP | `app/Http/Controllers/Api/CheckoutController.php` |
| Filament | `app/Filament/Checkout/` — read-only список и просмотр |
| SPA | `resources/js/stores/checkoutStore.js`, `resources/js/features/checkout/`, док `CartDockPanel` |

## Аудит (состояние на 2026-06-14)

### Готово

- Полная вертикаль Domain → Application → Infrastructure → HTTP (6 команд).
- Агрегат с инвариантами: мутации только в `draft`, confirm требует все 4 блока + непустую корзину.
- SPA: bootstrap сессии, визард в доке корзины, PATCH-блоки, confirm.
- `CheckoutServiceProvider`: репозиторий, `CatalogPricingPort`, listener на `CheckoutConfirmed`.

### Пробелы / техдолг

| # | Тема | Детали |
|---|------|--------|
| 1 | **GET checkout** | `GET /api/checkout/{id}` — **реализовано**; SPA восстанавливает с сервера, fallback на sessionStorage |
| 2 | **Order BC** | `OnCheckoutConfirmed` → `CreateOrderUseCase` — **реализовано** |
| 3 | **Тесты** | Нет feature/unit на use case и API |
| 4 | **Legacy HTTP** | ~~`app/Http/Requests/Shopping/*` и `shoppingApi.js`~~ — **удалено** |
| 5 | **Enum reuse** | `DeliveryMethod` / `PaymentMethod` вынесены в `App\Shared\Enum\` |
| 6 | **Акции / подарки** | Нет блока promotions в агрегате; UI подарков на фронте без бекенда |
| 7 | **Тариф доставки** | Сумма корзины без delivery fee; Delivery BC не подключён к расчёту |
| 8 | **Auth** | Checkout API публичный, без привязки к Sanctum / cookie-сессии |
| 9 | **Исключения HTTP** | Domain exceptions не мапятся в 404/409 в `Handler` |

### Рекомендуемый порядок доработок

1. ~~`GetCheckoutUseCase` + GET endpoint~~ — **сделано**.
2. Order BC: handler `OnCheckoutConfirmed` → `CreateOrderFromCheckout`.
3. Feature-тесты на happy-path и confirm guards.
4. ~~Удалить legacy Shopping requests + dead `shoppingApi`~~ — **сделано**.
5. Вынести shared enums в Order/Shared или оставить в Checkout с явной матрицей зависимостей.
