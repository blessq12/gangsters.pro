import { routeRecords } from "./routeRecords.js";

/** Поля meta, относящиеся к публичным ссылкам шапки / mobile sheet. */
type PublicNavRouteMeta = {
    navLabel?: string;
    navHeaderLeftOrder?: number;
    navHeaderRightOrder?: number;
};

type RouteRecordLike = (typeof routeRecords)[number];

function navMeta(r: RouteRecordLike): PublicNavRouteMeta {
    const m = r.meta;
    if (!m || typeof m !== "object") {
        return {};
    }
    return m as PublicNavRouteMeta;
}

export type PublicNavRouteName = "home" | "about" | "delivery" | "contacts";

export type NavLinkItem = {
    routeName: PublicNavRouteName;
    label: string;
};

function buildNavByOrder(
    pickOrder: (m: PublicNavRouteMeta) => number | undefined,
): NavLinkItem[] {
    const staged: { order: number; item: NavLinkItem }[] = [];

    for (const r of routeRecords) {
        if (!r.name || typeof r.name !== "string") {
            continue;
        }
        const m = navMeta(r);
        const order = pickOrder(m);
        if (order === undefined || m.navLabel === undefined) {
            continue;
        }
        staged.push({
            order,
            item: {
                routeName: r.name as PublicNavRouteName,
                label: m.navLabel,
            },
        });
    }

    staged.sort((a, b) => a.order - b.order);
    return staged.map((s) => s.item);
}

export const NAV_LINKS_LEFT_PRIMARY: readonly NavLinkItem[] =
    buildNavByOrder((m) => m.navHeaderLeftOrder);

export const NAV_LINKS_RIGHT_PRIMARY: readonly NavLinkItem[] =
    buildNavByOrder((m) => m.navHeaderRightOrder);

export const NAV_LINKS_MOBILE_SHEET: readonly NavLinkItem[] = [
    ...NAV_LINKS_LEFT_PRIMARY,
    ...NAV_LINKS_RIGHT_PRIMARY,
];
