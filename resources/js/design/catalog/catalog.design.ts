/**
 * Каталог: оболочки категорий, контролов сетки, списков товаров.
 * Карточки и модалка: {@link catalogCards.design}, {@link catalogModal.design}.
 */

import { dockDesign } from "../layout/dock.design";
import { catalogCardsDesign } from "./catalogCards.design";
import { catalogModalDesign } from "./catalogModal.design";
import { catalogSearchDesign } from "./catalogSearch.design";

const { chromePillActive, chromePillInactive } = dockDesign.shared;
const categoryPillBase =
    "whitespace-nowrap rounded-none border transition-colors backdrop-blur";

export const catalogDesign = {
    /** CatalogCategoriesBase: остров и пиллы категорий (desktop/mobile через variant). */
    categories: {
        outer: "relative mb-10 w-full min-w-0 max-w-full",
        island:
            "min-w-0 max-w-full rounded-none border border-app-accent/40 bg-app-canvas shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur",
        islandPaddingMobile: "px-4 py-4",
        islandPaddingDesktop: "px-4 py-3.5 lg:px-8",
        rowMobile:
            "cats-scroll flex items-center gap-2 overflow-x-auto py-2",
        rowDesktop: "flex flex-wrap items-center gap-2 pb-1.5",
        pillBase: categoryPillBase,
        pillSizingMobile: "px-4 py-2 text-xs",
        pillSizingDesktop: "px-5 py-2 text-sm",
        pillActiveMobile: chromePillActive,
        pillActiveDesktop: chromePillActive,
        pillInactive: chromePillInactive,
    },

    /** CatalogViewControls * */
    viewControls: {
        desktop: {
            wrapper:
                "inline-flex items-center gap-2 bg-app-glass-fill p-1 backdrop-blur",
            label: "px-2 text-xs font-medium text-app-muted",
            btn: "px-3 py-1.5 text-xs font-semibold transition",
            btnActive: "bg-app-accent text-black",
            btnInactive:
                "text-app-muted hover:bg-black/8 hover:text-app-canvas-fg",
        },
        mobile: {
            wrapper:
                "inline-flex w-full items-center bg-app-glass-fill p-1 backdrop-blur",
            btn: "flex-1 px-3 py-2 text-xs font-semibold transition",
            btnActive: "bg-app-accent text-black",
            btnInactive:
                "text-app-muted hover:bg-black/8 hover:text-app-canvas-fg",
        },
    },

    /** CatalogProductsBase: секции и сообщения. */
    products: {
        root: "space-y-4",
        loading: "text-sm text-app-muted",
        empty: "text-sm text-app-muted",
        loadingText: "Загружаем вкусняшки…",
        emptyText: "Тут пока тихо. Выберите другую категорию.",
        sectionsStack: "space-y-7",
        sectionTitle:
            "mb-4 text-xl font-bold uppercase tracking-wider text-app-accent sm:text-2xl sm:mb-5",
    },

    cards: catalogCardsDesign,
    modal: catalogModalDesign,
    search: catalogSearchDesign,
} as const;

export type CatalogDesign = typeof catalogDesign;
