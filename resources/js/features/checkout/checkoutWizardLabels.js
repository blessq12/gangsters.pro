import { CHECKOUT_WIZARD_GROUPS } from "./checkoutWizardGroups";

/** Заголовок dock-панели по активному шагу. */
export function resolveCheckoutDockTitle(step) {
    return CHECKOUT_WIZARD_GROUPS[step] ?? CHECKOUT_WIZARD_GROUPS.cart;
}

/** Подсказка под заголовком dock (одна строка). */
export const CHECKOUT_STEP_HINTS = Object.freeze({
    cart: "Проверь состав заказа перед оформлением.",
    guest: "Позвоним для подтверждения заказа.",
    delivery: "Укажи, как и куда доставить заказ.",
    payment: "Выбери способ оплаты при получении.",
    confirm: "Проверь данные и отправь заказ.",
    success: null,
});

export const CHECKOUT_NAV_LABELS = Object.freeze({
    back: "Назад",
    cartPrimary: "Оформить заказ",
    next: "Далее",
    confirm: "Отправить заказ",
    success: "В меню",
    authLink: "Уже есть аккаунт? Войти",
});
