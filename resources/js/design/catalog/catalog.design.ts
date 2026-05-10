/**
 * Каталог: оболочки категорий, контролов сетки, списков товаров.
 * Карточки и модалка: {@link catalogCards.design}, {@link catalogModal.design}.
 */

import { catalogCardsDesign } from "./catalogCards.design";
import { catalogModalDesign } from "./catalogModal.design";

export const catalogDesign = {
    /** CatalogCategoriesBase: остров и пиллы категорий (desktop/mobile через variant). */
    categories: {
        outer: "relative mb-10 w-full min-w-0 max-w-full",
        island:
            "min-w-0 max-w-full border border-amber-400/30 bg-[rgba(255,255,255,0.035)] shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur",
        islandPaddingMobile: "px-4 py-4",
        islandPaddingDesktop: "px-4 py-3.5 lg:px-8",
        rowMobile:
            "cats-scroll flex items-center gap-2 overflow-x-auto py-2",
        rowDesktop: "flex flex-wrap items-center gap-2 pb-1.5",
        pillBase:
            "whitespace-nowrap border transition-colors backdrop-blur bg-[rgba(0,0,0,0.75)]",
        pillSizingMobile: "px-4 py-2 text-xs",
        pillSizingDesktop: "px-5 py-2 text-sm",
        pillActiveMobile:
            "border-amber-400/70 text-amber-100 shadow-[0_0_10px_rgba(251,191,36,0.4)]",
        pillActiveDesktop:
            "border-amber-400/70 text-amber-100 shadow-[0_0_14px_rgba(251,191,36,0.45)]",
        pillInactive:
            "border-white/10 text-slate-300 hover:border-amber-400/50 hover:text-amber-200",
    },

    /** Устаревший CatalogCategories.vue (responsive без Base) — отдельные пиллы. */
    categoriesLegacy: {
        outer: "relative mb-10 w-full min-w-0 max-w-full",
        island:
            "min-w-0 max-w-full border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-4 sm:px-6 lg:px-8 py-3.5 shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur",
        row: "flex flex-wrap items-center gap-3 pb-1.5",
        pillBase:
            "whitespace-nowrap border px-5 py-2 text-xs sm:text-sm md:text-[0.9rem] transition-colors backdrop-blur bg-[rgba(0,0,0,0.75)]",
        pillActive:
            "border-amber-400/70 text-amber-100 shadow-[0_0_14px_rgba(251,191,36,0.45)]",
        pillInactive:
            "border-white/10 text-slate-300 hover:border-amber-400/50 hover:text-amber-200",
    },

    /** CatalogViewControls * */
    viewControls: {
        desktop: {
            wrapper:
                "inline-flex items-center gap-2 border border-white/10 bg-black/30 p-1",
            label: "px-2 text-xs font-medium text-slate-400",
            btn: "px-3 py-1.5 text-xs font-semibold transition",
            btnActive: "bg-amber-400 text-black",
            btnInactive:
                "text-slate-300 hover:bg-white/10 hover:text-slate-100",
        },
        mobile: {
            wrapper:
                "inline-flex w-full items-center border border-white/10 bg-black/30 p-1",
            btn: "flex-1 px-3 py-2 text-xs font-semibold transition",
            btnActive: "bg-amber-400 text-black",
            btnInactive:
                "text-slate-300 hover:bg-white/10 hover:text-slate-100",
        },
    },

    /** CatalogProductsBase: секции и сообщения. */
    products: {
        root: "space-y-4",
        loading: "text-sm text-slate-400",
        empty: "text-sm text-slate-500",
        loadingText: "Загружаем вкусняшки…",
        emptyText: "Тут пока тихо. Выберите другую категорию.",
        sectionsStack: "space-y-7",
        sectionTitle:
            "mb-3 text-sm font-semibold uppercase tracking-wide text-amber-200/90",
    },

    /** CatalogProducts.vue: плоский список (masonry — scoped в SFC). */
    productsFlat: {
        root: "space-y-4",
        loading: "text-sm text-slate-400",
        empty: "text-sm text-slate-500",
        loadingText: "Загружаем вкусняшки…",
        emptyText: "Тут пока тихо. Выберите другую категорию.",
    },

    cards: catalogCardsDesign,
    modal: catalogModalDesign,
} as const;

export type CatalogDesign = typeof catalogDesign;
