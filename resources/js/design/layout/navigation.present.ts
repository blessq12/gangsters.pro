/** Модель публичных ссылок навигации (подписи + имя роута Vue Router). */

export type PublicNavRouteName =
    | "home"
    | "about"
    | "delivery"
    | "contacts";

export type NavLinkItem = {
    routeName: PublicNavRouteName;
    label: string;
};

export const NAV_LINKS_LEFT_PRIMARY: readonly NavLinkItem[] = [
    { routeName: "home", label: "Главная" },
    { routeName: "about", label: "О компании" },
] as const;

export const NAV_LINKS_RIGHT_PRIMARY: readonly NavLinkItem[] = [
    { routeName: "delivery", label: "Оплата и доставка" },
    { routeName: "contacts", label: "Контакты" },
] as const;

/** Полоса меню drawer (mobile): тот же порядок контента, что и раньше. */
export const NAV_LINKS_MOBILE_SHEET: readonly NavLinkItem[] = [
    ...NAV_LINKS_LEFT_PRIMARY,
    ...NAV_LINKS_RIGHT_PRIMARY,
] as const;
