/**
 * Bottom dock presentation (DockChrome): full-width island on mobile,
 * compact w-fit pill from md (tablet/desktop).
 */

/** Same as `navbar.*.bar`: dense island on canvas. */
const dockChromeIslandSurface =
    "rounded-none border border-app-accent/40 bg-app-canvas backdrop-blur";

/** Pill / icon on chrome island (dock tabs, category bar). */
const chromePillActive =
    "border-app-accent/70 bg-neutral-950/88 text-app-accent";
const chromePillInactive =
    "border-black/20 bg-neutral-950/88 text-app-canvas-fg hover:border-app-accent/50 hover:text-app-accent";

export const dockDesign = {
    shared: {
        chromeIslandSurface: dockChromeIslandSurface,
        chromePillActive,
        chromePillInactive,
        tabIconActive: chromePillActive,
        tabIconInactive: chromePillInactive,
        chromeScrollTransform:
            "transition-[transform,opacity] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] will-change-transform",
        badge: "absolute -top-1.5 -right-1.5 flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-none bg-red-500 px-1 text-[10px] font-semibold text-white",
        panelScrim: "fixed inset-0 z-[25] bg-black/45 backdrop-blur-[2px]",
    },

    chrome: {
        fixedRoot:
            "pointer-events-none fixed inset-x-0 bottom-4 z-30 md:bottom-6",
        visibleInner:
            "mx-auto flex w-full max-w-7xl flex-col items-center px-4 sm:px-6 md:px-6 lg:px-8",
        visibleInnerWithPanel:
            "min-h-0 max-h-[calc(100dvh-1rem-env(safe-area-inset-bottom,0px))] justify-end md:max-h-[calc(100dvh-1.5rem)]",
        panelOuter: "pointer-events-auto mb-3 w-full max-w-4xl min-h-0 md:max-w-2xl",
        panelOuterExpanded: "flex flex-1 flex-col",
        dockIsland: `pointer-events-auto ${dockChromeIslandSurface} flex w-full shrink-0 items-stretch justify-between gap-3 px-3 py-3 sm:gap-4 sm:px-4 sm:py-4 md:w-fit md:justify-start md:px-4 md:py-3.5`,
        islandDivider: "w-px shrink-0 self-stretch bg-app-accent/25",
        tabRow: "flex shrink-0 items-center gap-2.5 sm:gap-3",
        tabButton: "group flex items-center justify-center transition-colors",
        tabIconWrap:
            "relative flex h-11 w-11 items-center justify-center rounded-none border transition-colors sm:h-12 sm:w-12",
        tabIconMdiSize: "text-lg sm:text-xl",
    },

    cartSummary: {
        root: "relative flex min-w-0 flex-1 flex-col items-start justify-center gap-0.5 overflow-hidden rounded-none border px-4 py-1.5 text-left transition-[color,background-color,border-color] duration-200 ease-out sm:min-w-[11rem] sm:px-5 md:min-w-[9.5rem] md:flex-none",
        rootActive: chromePillActive,
        rootInactive: chromePillInactive,
        rootFlash: "border-app-accent bg-app-accent text-white",
        sheen: "dock-cart-summary-sheen pointer-events-none absolute inset-0 z-0",
        content:
            "relative z-10 flex w-full min-h-[2.5rem] flex-col items-stretch justify-center overflow-hidden",
        idleLayer:
            "flex w-full flex-col items-start justify-center gap-0.5 transition-opacity duration-200 ease-out",
        idleLayerHidden: "pointer-events-none opacity-0",
        flashLayer: "absolute inset-0 flex items-center justify-center",
        amount: "text-sm font-semibold leading-tight tabular-nums sm:text-base",
        qty: "text-[11px] leading-tight text-app-muted tabular-nums sm:text-xs",
        flashLabel:
            "w-full text-center text-sm font-semibold uppercase tracking-wide leading-tight sm:text-base",
        emptyWrap: "flex items-center gap-2",
        emptyIcon: "mdi mdi-cart-outline text-lg leading-none sm:text-xl",
        emptyLabel:
            "max-w-[7rem] text-[11px] leading-snug sm:max-w-[8rem] sm:text-xs",
    },

    /** Flash «добавлено» на табе избранного — как cartSummary, вместо текста иконка сердца. */
    favoritesTab: {
        wrap: "relative flex h-11 w-11 items-center justify-center overflow-hidden rounded-none border transition-[color,background-color,border-color] duration-200 ease-out sm:h-12 sm:w-12",
        wrapFlash: "border-app-accent bg-app-accent text-white",
        idleLayer:
            "relative flex h-full w-full items-center justify-center transition-opacity duration-200 ease-out",
        idleLayerHidden: "pointer-events-none opacity-0",
        flashLayer: "absolute inset-0 flex items-center justify-center",
        flashIcon: "mdi mdi-heart text-lg leading-none sm:text-xl",
        iconMdiSize: "text-lg sm:text-xl",
    },

    /** Таб профиля: гость — outline-иконка; авторизован — красный квадрат с монограммой. */
    profileTab: {
        wrap: "relative flex h-11 w-11 items-center justify-center overflow-hidden rounded-none border transition-[color,background-color,border-color] duration-200 ease-out sm:h-12 sm:w-12",
        wrapAuthed: "border-app-accent bg-app-accent text-white",
        monogram:
            "text-sm font-semibold uppercase leading-none tracking-wide sm:text-base",
        iconMdiSize: "text-lg sm:text-xl",
    },
} as const;

export type DockDesign = typeof dockDesign;
