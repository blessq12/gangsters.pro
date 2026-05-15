/**
 * Баннер установки PWA (Chromium beforeinstallprompt).
 */

import { shellTypography } from "./shell.design";

export const pwaInstallDesign = {
    fixedRoot:
        "pointer-events-none fixed inset-x-0 z-[28] max-md:bottom-28 md:bottom-6",
    inner: "pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6 lg:px-8",
    bar: "flex flex-wrap items-center justify-between gap-3 rounded-none border border-app-accent/35 bg-app-glass-fill px-4 py-3 shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur",
    text: `${shellTypography.body.default} text-app-canvas-fg`,
    actions: "flex shrink-0 items-center gap-2",
    installButton:
        "rounded-none border border-app-accent/50 bg-app-accent px-3 py-1.5 text-sm font-medium text-black transition-colors hover:bg-app-accent-hover",
    dismissButton:
        "rounded-none px-2 py-1.5 text-sm text-app-muted transition-colors hover:text-app-canvas-fg",
} as const;

export type PwaInstallDesign = typeof pwaInstallDesign;
