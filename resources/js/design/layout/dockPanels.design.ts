/**
 * Контент-панели дока (Cart / Profile / Favorites / Delivery).
 * Компоненты: resources/js/components/layout/dock/panels/*.vue
 * Презентация вынесена из SFC; логика и данные остаются в компонентах.
 */

export const dockPanelsDesign = {
    shared: {
        /** Общая оболочка простых панелей (cart, profile, favorites). */
        shell:
            "rounded-none border border-app-accent/30 bg-[rgba(0,0,0,0.88)] px-4 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur sm:px-6 lg:px-8",
        typography: {
            panelTitle:
                "font-heading text-base font-normal sm:text-lg text-app-accent",
            metaLine: "text-[11px] text-app-muted",
            sectionLabelUppercase:
                "mb-2 text-xs font-medium uppercase tracking-wide text-app-muted",
        },
        stackCart: "flex flex-col gap-3 lg:gap-4",
        headerRowFlex: "flex items-center justify-between gap-3",
        stackSimple: "flex flex-col gap-3",
        minWidth0: "min-w-0",
    },

    cart: {
        headerBadge:
            "flex h-8 items-center rounded-none bg-neutral-950/88 px-3 text-xs text-app-canvas-fg",
    },

    profile: {
        headerRow: "mb-3 flex items-center gap-2",
        avatar:
            "flex h-10 w-10 items-center justify-center rounded-none border border-app-accent/40 bg-neutral-950/88 text-sm font-semibold text-app-accent shadow-[0_0_16px_rgba(198,36,36,0.6)]",
        nameLine: "text-xs font-semibold text-app-canvas-fg",
        tabRow: "mb-3 flex flex-wrap gap-2 text-[11px] font-medium",
        tabs: {
            base: "rounded-none px-3 py-1 transition",
            active:
                "bg-app-accent text-black shadow-[0_0_14px_rgba(198,36,36,0.7)]",
            inactive: "bg-black/5 text-app-canvas-fg hover:bg-black/8",
        },
        contentStack: "space-y-3",
        ordersMinHeight: "min-h-[12rem]",
    },

    favorites: {
        emptyState:
            "rounded-none bg-app-accent-soft-bg px-4 py-5 text-sm text-app-muted",
        ul: "space-y-2 text-xs sm:text-sm text-app-canvas-fg",
        row: "flex items-center justify-between gap-3 rounded-none bg-app-accent-soft-bg px-3 py-2",
        productName: "truncate font-medium text-app-canvas-fg",
        productPrice: "mt-0.5 text-[11px] text-app-muted",
        actionRow: "flex items-center gap-3",
        actionAddToCart:
            "shrink-0 text-[11px] text-app-accent transition-colors hover:text-app-accent",
        actionRemove:
            "shrink-0 text-[11px] text-app-muted transition-colors hover:text-red-400",
    },

    delivery: {
        root: "relative w-full min-w-0 max-w-full overflow-hidden rounded-none border border-app-accent/30 bg-[rgba(0,0,0,0.9)] px-4 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur sm:px-6 lg:px-8",
        decorLayer:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        decorBlobLeft:
            "absolute -left-10 top-0 h-40 w-40 rounded-full bg-app-accent/10 blur-3xl",
        decorBlobRight:
            "absolute -right-8 bottom-0 h-48 w-48 rounded-full bg-app-accent/12 blur-3xl",
        contentColumn:
            "relative w-full min-w-0 space-y-4 text-xs text-app-canvas-fg sm:text-sm",
        header:
            "w-full min-w-0 rounded-none border border-app-border-on-surface bg-[linear-gradient(180deg,rgba(0,0,0,0.04),rgba(0,0,0,0.03))] px-4 py-4 sm:px-5",
        kickerChip:
            "mb-2 inline-flex rounded-none border border-app-accent/30 bg-app-accent/10 px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-app-accent",
        headline:
            "text-xl font-normal text-app-accent sm:text-2xl lg:text-3xl",
        loadingLine: "mt-2 text-sm text-app-muted",
        statsSection: "mt-4 w-full min-w-0 border-t border-app-border-on-surface pt-4",
        statsRowFlex: "flex flex-row flex-wrap gap-2",
        statSkeletonCard:
            "min-w-0 flex-1 basis-full rounded-none border border-app-border-on-surface bg-[rgba(0,0,0,0.78)] px-4 py-3 backdrop-blur sm:basis-auto",
        statCard:
            "min-w-0 flex-1 basis-[7.5rem] rounded-none border border-app-border-on-surface bg-[rgba(0,0,0,0.78)] px-3 py-3 backdrop-blur sm:px-4",
        statLabel:
            "text-[11px] uppercase tracking-[0.2em] text-app-muted",
        statValueSkeleton: "mt-1 font-semibold text-app-accent/90",
        statValue:
            "mt-1 min-w-0 wrap-break-word text-left text-sm font-semibold leading-relaxed text-app-accent sm:text-base",
        mapFrame:
            "w-full min-w-0 overflow-hidden rounded-none border border-app-border-on-surface bg-[rgba(0,0,0,0.78)] shadow-[0_18px_50px_rgba(0,0,0,0.45)] backdrop-blur",
        mapIframe: "h-56 w-full min-w-0 border-0 sm:h-64 lg:h-72 xl:h-80",
        mapFallbackDashed:
            "grid min-h-[14rem] w-full min-w-0 place-content-center rounded-none border border-dashed border-black/15 bg-app-surface px-4 py-8 text-center text-sm text-neutral-600 sm:min-h-[16rem]",
        mapFallbackProse: "mx-auto max-w-prose leading-relaxed",
        mapLoadingBox:
            "grid min-h-[10rem] w-full place-content-center rounded-none border border-app-border-on-surface bg-app-surface text-sm text-neutral-600",
        conditionsSection:
            "w-full min-w-0 rounded-none border border-app-border-on-surface bg-[rgba(0,0,0,0.78)] px-4 py-4 backdrop-blur sm:px-5",
        conditionsHeading:
            "text-[11px] font-medium uppercase tracking-[0.22em] text-app-muted",
        conditionsDl:
            "mt-3 flex w-full min-w-0 flex-row flex-wrap gap-2",
        conditionCard:
            "min-w-0 flex-1 basis-[8rem] rounded-none border border-app-border-on-surface bg-[rgba(0,0,0,0.78)] px-3 py-3 backdrop-blur sm:px-4",
        conditionDt:
            "text-[11px] uppercase tracking-[0.18em] text-app-muted",
        conditionDd:
            "mt-1.5 min-w-0 wrap-break-word text-pretty text-base font-semibold leading-relaxed text-app-accent",
    },
} as const;

export type DockPanelsDesign = typeof dockPanelsDesign;
