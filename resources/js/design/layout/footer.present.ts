import type { PublicNavRouteName } from "./navigation.present";

export type FooterPrimaryNavItem = {
    routeName: Extract<PublicNavRouteName, "about" | "delivery" | "contacts">;
    label: string;
};

export const FOOTER_PRIMARY_NAV: readonly FooterPrimaryNavItem[] = [
    { routeName: "about", label: "О компании" },
    { routeName: "delivery", label: "Оплата и доставка" },
    { routeName: "contacts", label: "Контакты" },
] as const;
