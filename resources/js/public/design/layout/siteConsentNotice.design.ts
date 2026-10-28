/**
 * mobile full-width снизу, desktop — компактный правый нижний угол.
 */

import { shellColorRoles } from "./shell.design";

export const siteConsentNoticeDesign = {
    root:
        "pointer-events-none fixed inset-x-0 bottom-[5.5rem] z-[45] px-4 " +
        "md:inset-x-auto md:bottom-6 md:right-6 md:w-[min(100%-3rem,22rem)] md:px-0 " +
        "lg:right-8 lg:bottom-8",
    panel:
        "pointer-events-auto flex w-full flex-col gap-3 border border-app-accent/40 bg-app-canvas px-4 py-3 shadow-2xl shadow-black/50 backdrop-blur " +
        "sm:flex-row sm:items-center sm:gap-4 sm:px-5 sm:py-3.5 " +
        "md:gap-2.5 md:px-3.5 md:py-2",
    text: `min-w-0 flex-1 text-sm leading-snug md:text-xs md:leading-snug ${shellColorRoles.canvasFgSoft}`,
    textConsentSuffix: "md:hidden",
    action:
        "inline-flex shrink-0 items-center justify-center self-stretch border border-transparent bg-app-accent px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-app-accent-hover " +
        "sm:self-auto md:px-3.5 md:py-1.5 md:text-xs",
} as const;

export type SiteConsentNoticeDesign = typeof siteConsentNoticeDesign;
