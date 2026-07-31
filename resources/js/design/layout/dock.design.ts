/**
 * Bottom dock presentation (DockChrome): one horizontal island for all breakpoints.
 */

import { shellTypography } from "./shell.design";

/** Same as `navbar.*.bar`: dense island on canvas. */
const dockChromeIslandSurface =
    "rounded-none border border-app-accent/40 bg-app-canvas shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur";

/** Pill / icon on chrome island (dock tabs, category bar). */
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
        panelScrim:
            "fixed inset-0 z-[25] bg-black/45 backdrop-blur-[2px]",
    },

    chrome: {
        fixedRoot:
            "pointer-events-none fixed inset-x-0 bottom-4 z-30 md:bottom-6",
        visibleInner:
            "pointer-events-auto mx-auto flex w-full max-w-7xl flex-col items-center px-4 sm:px-6 md:px-6 lg:px-8",
        visibleInnerWithPanel:
            "min-h-0 max-h-[calc(100dvh-1rem-env(safe-area-inset-bottom,0px))] justify-end md:max-h-[calc(100dvh-1.5rem)]",
        panelOuter:
            "mb-3 w-full max-w-4xl min-h-0 md:max-w-5xl",
        panelOuterExpanded:
            "flex flex-1 flex-col",
        dockIsland: `${dockChromeIslandSurface} flex w-fit shrink-0 items-center px-3 py-3 sm:px-4 sm:py-4 md:px-4 md:py-3.5`,
        tabRow: "flex items-center gap-2.5 sm:gap-3",
        tabButton: shellTypography.body.dockTabRow,
        tabIconWrap:
            "relative flex h-11 w-11 items-center justify-center rounded-none border transition-colors sm:h-12 sm:w-12",
        tabIconMdiSize: "text-lg sm:text-xl",
        tabLabelVisibility: "hidden text-[11px] lg:block",
        tabLabelActive: "text-app-accent",
        tabLabelInactive: "text-app-muted group-hover:text-app-accent",
    },
} as const;

export type DockDesign = typeof dockDesign;
