/**
 * Презентация нижнего / бокового dock (DockChrome): mobile остров + desktop rail.
 */

import { shellTypography } from "./shell.design";

/** Как у `navbar.*.bar`: плотный остров на канвасе (вариант A). */
const dockChromeIslandSurface =
    "rounded-none border border-app-accent/40 bg-app-canvas shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur";

/** Пилла/иконка на chrome-острове (dock tabs, бар категорий). */
const chromePillActive =
    "border-app-accent/70 bg-neutral-950/88 text-app-accent shadow-[0_0_18px_rgba(198,36,36,0.7)]";
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
        badge:
            "absolute -top-1.5 -right-1.5 flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-none bg-red-500 px-1 text-[10px] font-semibold text-white shadow-[0_0_8px_rgba(239,68,68,0.65)]",
    },

    mobile: {
        fixedRoot:
            "pointer-events-none fixed inset-x-0 bottom-4 z-30",
        visibleInner:
            "pointer-events-auto mx-auto flex w-full max-w-7xl flex-col items-center px-4 sm:px-6",
        visibleInnerWithPanel:
            "min-h-0 max-h-[calc(100dvh-1rem-env(safe-area-inset-bottom,0px))] justify-end",
        panelOuter:
            "mb-3 w-full max-w-4xl min-h-0",
        panelOuterExpanded:
            "flex flex-1 flex-col",
        dockIsland: `${dockChromeIslandSurface} flex w-fit shrink-0 items-center px-3 py-3 sm:px-4 sm:py-4`,
        tabRow: "flex items-center gap-2.5 sm:gap-3",
        tabButton: shellTypography.body.dockTabRow,
        tabIconWrap:
            "relative flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-none border transition-colors",
        tabIconMdiSize: "text-lg sm:text-xl",
        tabLabelVisibility: "hidden lg:block",
        tabLabelActive: "text-app-accent",
        tabLabelInactive: "text-app-muted group-hover:text-app-accent",
    },

    desktop: {
        fixedRoot:
            "pointer-events-none fixed inset-y-0 left-0 z-30 flex items-center pl-6 sm:pl-10 lg:pl-12 xl:pl-14",
        chromeIsland: `${dockChromeIslandSurface} pointer-events-auto flex max-h-[min(88vh,920px)] max-w-[min(96vw,960px)] items-start gap-4 overflow-y-auto px-3 py-3`,
        chromeIslandWithPanel:
            "h-[min(92vh,960px)] max-h-[min(92vh,960px)] items-stretch",
        tabColumn: "flex shrink-0 flex-col items-center gap-2",
        tabButton:
            "group flex flex-col items-center gap-2 transition-colors",
        tabIconWrap:
            "relative flex h-10 w-10 items-center justify-center rounded-none border transition-colors",
        tabIconMdiSize: "text-lg",
        tabLabelHidden: `${shellTypography.body.dockTabLabelDesktop} text-app-muted group-hover:text-app-accent`,
        tabLabelActive: "text-app-accent",
        tabLabelInactiveMuted: "text-app-muted",
        desktopPanelOuter: "mt-0 mb-0 w-[520px] max-w-[70vw] min-h-0",
        desktopPanelOuterExpanded:
            "flex h-full min-h-0 flex-col self-stretch",
    },
} as const;

export type DockDesign = typeof dockDesign;
