/**
 * Контент-панели дока (Cart / Profile / Favorites / Delivery).
 * Компоненты: resources/js/components/layout/dock/panels/*.vue
 * Презентация вынесена из SFC; логика и данные остаются в компонентах.
 */

export const dockPanelsDesign = {
    shared: {
        /** Общая оболочка простых панелей (cart, profile, favorites). */
        shell:
            "rounded-none border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur sm:px-6 lg:px-8",
        typography: {
            panelTitle: "text-sm sm:text-base font-semibold text-slate-50",
            metaLine: "text-[11px] text-slate-400",
            sectionLabelUppercase:
                "mb-2 text-xs font-medium uppercase tracking-wide text-slate-400",
        },
        stackCart: "flex flex-col gap-3 lg:gap-4",
        headerRowFlex: "flex items-center justify-between gap-3",
        stackSimple: "flex flex-col gap-3",
        minWidth0: "min-w-0",
    },

    cart: {
        headerBadge:
            "flex h-8 items-center rounded-none bg-black/70 px-3 text-xs text-slate-200",
    },

    profile: {
        headerRow: "mb-3 flex items-center gap-2",
        avatar:
            "flex h-10 w-10 items-center justify-center rounded-none border border-amber-400/40 bg-black/70 text-sm font-semibold text-amber-200 shadow-[0_0_16px_rgba(251,191,36,0.6)]",
        nameLine: "text-xs font-semibold text-slate-50",
        tabRow: "mb-3 flex flex-wrap gap-2 text-[11px] font-medium",
        tabs: {
            base: "rounded-none px-3 py-1 transition",
            active:
                "bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]",
            inactive: "bg-white/5 text-slate-200 hover:bg-white/10",
        },
        contentStack: "space-y-3",
        ordersMinHeight: "min-h-[12rem]",
    },

    favorites: {
        emptyState:
            "rounded-none bg-[rgba(255,255,255,0.03)] px-4 py-5 text-sm text-slate-300",
        ul: "space-y-2 text-xs sm:text-sm text-slate-200",
        row: "flex items-center justify-between gap-3 rounded-none bg-[rgba(255,255,255,0.03)] px-3 py-2",
        productName: "truncate font-medium text-slate-100",
        productPrice: "mt-0.5 text-[11px] text-slate-400",
        actionRow: "flex items-center gap-3",
        actionAddToCart:
            "shrink-0 text-[11px] text-amber-300 transition-colors hover:text-amber-200",
        actionRemove:
            "shrink-0 text-[11px] text-slate-400 transition-colors hover:text-red-400",
    },

    delivery: {
        root: "relative w-full min-w-0 max-w-full overflow-hidden rounded-none border border-amber-400/30 bg-[rgba(0,0,0,0.9)] px-4 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur sm:px-6 lg:px-8",
        decorLayer:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        decorBlobLeft:
            "absolute -left-10 top-0 h-40 w-40 rounded-full bg-amber-500/10 blur-3xl",
        decorBlobRight:
            "absolute -right-8 bottom-0 h-48 w-48 rounded-full bg-rose-500/10 blur-3xl",
        contentColumn:
            "relative w-full min-w-0 space-y-4 text-xs text-slate-200 sm:text-sm",
        header:
            "w-full min-w-0 rounded-none border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.02))] px-4 py-4 sm:px-5",
        kickerChip:
            "mb-2 inline-flex rounded-none border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-amber-200",
        headline: "text-lg font-semibold text-slate-50 sm:text-xl",
        loadingLine: "mt-2 text-sm text-slate-400",
        statsSection: "mt-4 w-full min-w-0 border-t border-white/10 pt-4",
        statsRowFlex: "flex flex-row flex-wrap gap-2",
        statSkeletonCard:
            "min-w-0 flex-1 basis-full rounded-none border border-white/10 bg-black/25 px-4 py-3 sm:basis-auto",
        statCard:
            "min-w-0 flex-1 basis-[7.5rem] rounded-none border border-white/10 bg-black/25 px-3 py-3 sm:px-4",
        statLabel:
            "text-[11px] uppercase tracking-[0.2em] text-slate-400",
        statValueSkeleton: "mt-1 font-semibold text-amber-300/90",
        statValue:
            "mt-1 min-w-0 wrap-break-word text-left text-sm font-semibold leading-relaxed text-amber-300 sm:text-base",
        mapFrame:
            "w-full min-w-0 overflow-hidden rounded-none border border-white/10 bg-black/30 shadow-[0_18px_50px_rgba(0,0,0,0.45)]",
        mapIframe: "h-56 w-full min-w-0 border-0 sm:h-64 lg:h-72 xl:h-80",
        mapFallbackDashed:
            "grid min-h-[14rem] w-full min-w-0 place-content-center rounded-none border border-dashed border-white/15 bg-black/25 px-4 py-8 text-center text-sm text-slate-500 sm:min-h-[16rem]",
        mapFallbackProse: "mx-auto max-w-prose leading-relaxed",
        mapLoadingBox:
            "grid min-h-[10rem] w-full place-content-center rounded-none border border-white/10 bg-black/20 text-sm text-slate-500",
        conditionsSection:
            "w-full min-w-0 rounded-none border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-4 sm:px-5",
        conditionsHeading:
            "text-[11px] font-medium uppercase tracking-[0.22em] text-slate-400",
        conditionsDl:
            "mt-3 flex w-full min-w-0 flex-row flex-wrap gap-2",
        conditionCard:
            "min-w-0 flex-1 basis-[8rem] rounded-none border border-white/10 bg-black/20 px-3 py-3 sm:px-4",
        conditionDt:
            "text-[11px] uppercase tracking-[0.18em] text-slate-500",
        conditionDd:
            "mt-1.5 min-w-0 wrap-break-word text-pretty text-base font-semibold leading-relaxed text-amber-300",
    },
} as const;

export type DockPanelsDesign = typeof dockPanelsDesign;
