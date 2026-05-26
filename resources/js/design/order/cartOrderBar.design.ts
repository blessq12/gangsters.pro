/**
 * Sticky bar корзины над dock.
 * z-index: 40 — выше dock (z-30), ниже product modal (z-[9999]).
 */

import { shellTypography } from "../layout/shell.design";

export const cartOrderBarDesign = {
    /** fixedRoot: z-40 — см. комментарий выше */
    fixedRoot:
        "pointer-events-none fixed inset-x-0 bottom-[5.75rem] z-40 sm:bottom-[6.25rem]",
    inner: "pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6 lg:px-8",
    bar:
        "flex items-center gap-3 border border-app-accent/40 bg-app-canvas px-4 py-3 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur",
    summaryBtn:
        "min-h-11 min-w-0 flex-1 text-left transition-colors hover:text-app-accent",
    summaryLabel: `${shellTypography.body.default} font-semibold text-app-canvas-fg`,
    summaryMeta: "mt-0.5 text-xs text-app-muted",
    primaryBtn:
        "inline-flex min-h-11 shrink-0 items-center justify-center bg-app-accent px-4 py-2 text-sm font-semibold text-black transition-transform hover:scale-[1.02]",
} as const;

export type CartOrderBarDesign = typeof cartOrderBarDesign;
