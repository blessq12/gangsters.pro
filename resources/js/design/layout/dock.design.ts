/**
 * Презентация нижнего / бокового dock (DockChrome): mobile остров + desktop rail.
 */

export const dockDesign = {
    shared: {
        tabIconActive:
            "border-amber-400/70 bg-black/80 text-amber-200 shadow-[0_0_18px_rgba(251,191,36,0.7)]",
        tabIconInactive:
            "border-white/20 bg-black/70 text-slate-200 group-hover:border-amber-400/50 group-hover:text-amber-200",
        badge:
            "absolute -top-1.5 -right-1.5 flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-none bg-red-500 px-1 text-[10px] font-semibold text-white shadow-[0_0_8px_rgba(239,68,68,0.65)]",
    },

    mobile: {
        fixedRoot:
            "pointer-events-none fixed inset-x-0 bottom-4 z-30",
        visibleInner:
            "pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6",
        panelOuter:
            "mb-3 mx-auto w-full max-w-4xl",
        dockIsland:
            "mx-auto flex max-w-3xl items-center justify-center gap-4 rounded-none border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-5 sm:px-6 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur",
        tabRow: "flex items-center gap-3 sm:gap-4",
        tabButton:
            "group flex flex-col items-center gap-1.5 text-xs sm:text-xs transition-colors",
        tabIconWrap:
            "relative flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-none border transition-colors",
        tabIconMdiSize: "text-lg sm:text-xl",
        tabLabelVisibility: "hidden lg:block",
        tabLabelActive: "text-amber-200",
        tabLabelInactive: "text-slate-300 group-hover:text-amber-200",
    },

    desktop: {
        fixedRoot:
            "pointer-events-none fixed inset-y-0 left-0 z-30 flex items-center pl-6 sm:pl-10 lg:pl-12 xl:pl-14",
        chromeIsland:
            "pointer-events-auto flex max-h-[min(88vh,920px)] max-w-[min(96vw,960px)] items-start gap-4 overflow-y-auto rounded-none border border-amber-400/30 bg-[rgba(0,0,0,0.65)] px-3 py-3 backdrop-blur",
        tabColumn: "flex flex-col items-center gap-2",
        tabButton:
            "group flex flex-col items-center gap-2 transition-colors",
        tabIconWrap:
            "relative flex h-10 w-10 items-center justify-center rounded-none border transition-colors",
        tabIconMdiSize: "text-lg",
        tabLabelHidden:
            "hidden lg:block text-[11px] text-slate-300 group-hover:text-amber-200",
        tabLabelActive: "text-amber-200",
        tabLabelInactiveMuted: "text-slate-300",
        desktopPanelOuter: "mt-0 mb-0 w-[520px] max-w-[70vw]",
    },
} as const;

export type DockDesign = typeof dockDesign;
