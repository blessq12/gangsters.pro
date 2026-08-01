import { CHECKOUT_WIZARD_GROUPS } from "./checkoutWizardGroups";

/** Заголовок dock-панели по активному шагу (fallback; предпочтительно title из registry). */
export function resolveCheckoutDockTitle(step) {
    return CHECKOUT_WIZARD_GROUPS[step] ?? CHECKOUT_WIZARD_GROUPS.cart;
}

/** Подсказка под заголовком dock (одна строка). */
export const CHECKOUT_STEP_HINTS = Object.freeze({
    cart: "Проверь состав заказа перед оформлением.",
    guest: "Позвоним для подтверждения заказа.",
    fulfillment: "Сначала оплата, затем способ и адрес получения.",
    drinks: "Можно пропустить — напитки не обязательны.",
    confirm: "Проверь данные и отправь заказ.",
    success: null,
});

export const CHECKOUT_NAV_LABELS = Object.freeze({
    back: "Назад",
    cartPrimary: "Оформить заказ",
    next: "Далее",
    drinksPrimary: "Продолжить",
    confirm: "Отправить заказ",
    success: "В меню",
    authLink: "Уже есть аккаунт? Войти",
    editFulfillment: "Изменить оплату и доставку",
});
