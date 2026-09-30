/**
 * Классы футера: canvas-остров, колонки инфо, legal-полоса, модалки.
 */

import { shellColorRoles, shellTypography } from "./shell.design";

export const footerDesign = {
    footer: "relative z-[1] mt-10 pb-6",
    inner: "mx-auto max-w-7xl px-4 sm:px-6 lg:px-8",
    /** Слабее navbar (`/40`): вторичный chrome-остров. */
    bar: "rounded-none border border-app-accent/30 bg-app-canvas px-4 py-5 backdrop-blur sm:px-6 sm:py-6 lg:px-8",

    columns: "grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8",
    column: "min-w-0 flex flex-col gap-3",
    columnTitle: `${shellTypography.scale.body.overlineWide} ${shellColorRoles.muted}`,

    metaText: `text-sm leading-snug ${shellColorRoles.muted}`,
    metaStack: "flex flex-col gap-1.5",

    contactStack: "flex flex-col gap-1.5",
    contactPrimary: `text-sm font-medium leading-snug ${shellColorRoles.accent95} hover:text-app-accent transition-colors duration-200`,
    contactLink: `text-sm leading-snug ${shellColorRoles.canvasFg80} hover:text-app-accent transition-colors duration-200`,
    contactSocial: `text-sm leading-snug ${shellColorRoles.canvasFg80} hover:text-app-accent transition-colors duration-200`,
    contactMeta: `mt-1 text-sm leading-snug ${shellColorRoles.muted}`,

    linkStack: "flex flex-col items-start gap-1.5",
    linkItem: `text-left text-sm ${shellColorRoles.canvasFg80} hover:text-app-accent transition-colors duration-200`,

    legalFooter: "mt-6 flex flex-col gap-3 border-t border-app-accent/20 pt-4",
    legalBar:
        "flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4",
    legalIds: `min-w-0 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs leading-snug sm:text-sm ${shellColorRoles.muted}`,
    legalEntityName: `shrink-0 text-sm leading-snug sm:text-right ${shellColorRoles.canvasFgSoft}`,
    copyright: "text-xs opacity-70 sm:text-sm sm:text-right text-app-muted/80",
    footnote: `text-[11px] leading-snug  ${shellColorRoles.muted}`,

    legalHtml:
        "legal-doc text-sm text-app-canvas-fg/90 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-app-accent [&_a]:underline-offset-2 hover:[&_a]:underline [&_strong]:text-app-canvas-fg",
    modalFallback: "space-y-3 text-sm text-app-canvas-fg/90",
} as const;

export type FooterDesign = typeof footerDesign;
