import { CHECKOUT_WIZARD_GROUPS } from "./checkoutWizardGroups";

/** Заголовок dock-панели по активному шагу (fallback; предпочтительно title из registry). */
export function resolveCheckoutDockTitle(step) {
    return CHECKOUT_WIZARD_GROUPS[step] ?? CHECKOUT_WIZARD_GROUPS.cart;
}

/**
 * Реплика «официанта» на каждом шаге.
 * Корзина/success — вне нумерации flow.
 */
export const CHECKOUT_WAITER_LINES = Object.freeze({
    cart: "Проверьте состав заказа перед оформлением",
    upsell: "Удобно добавить к заказу сейчас — всё приедет вместе",
    guest: "Вход сохраняет контакты и адреса — следующее оформление быстрее",
    fulfillment: "Способ оплаты и получение заказа",
    confirm: "Проверьте состав, получение и оплату",
    success: "Заказ принят. Готовим",
});

/** @deprecated используй CHECKOUT_WAITER_LINES */
export const CHECKOUT_STEP_HINTS = CHECKOUT_WAITER_LINES;

export const CHECKOUT_NAV_LABELS = Object.freeze({
    back: "Назад",
    cartPrimary: "Далее",
    next: "Далее",
    upsellPrimary: "Продолжить",
    upsellSkip: "Без дополнений",
    guestPrimary: "Продолжить как гость",
    confirm: "Отправить заказ",
    success: "В меню",
    authLink: "Уже есть аккаунт? Войти",
    authRegisterCta: "Войти или зарегистрироваться",
    authRegisterEyebrow: "Удобство сервиса",
    authRegisterPitch: "Контакты и адреса сохраняются для следующих заказов",
    authRegisterBenefits: Object.freeze([
        "Быстрее оформление без повторного ввода",
        "Адреса доставки под рукой",
        "История заказов в профиле",
    ]),
    editFulfillment: "Изменить доставку и оплату",
});
