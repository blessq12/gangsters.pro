import { useCheckoutStore } from "../../stores/checkoutStore";

/**
 * Жизненный цикл checkout-сессии:
 * запуск приложения → создание/восстановление draft → оформление → очистка.
 */
export async function bootstrapCheckoutSession() {
    const checkoutStore = useCheckoutStore();
    await checkoutStore.bootstrapSession();
}
