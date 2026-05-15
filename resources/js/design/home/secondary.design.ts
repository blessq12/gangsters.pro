/**
 * Вторичные страницы: баннер-герой и контентный блок.
 */

import { secondarySurfaces, shellColorRoles } from "../layout/shell.design";

const island = secondarySurfaces.island;
const islandPad = secondarySurfaces.islandPad;

export const secondaryMarketingDesign = {
    banner: {
        section: "relative my-6 md:my-8 lg:my-12",
        sticksWrap: "rotate-15",
        stickLeft:
            "pointer-events-none absolute -bottom-2 right-6 h-3 w-auto",
        stickRight:
            "pointer-events-none absolute bottom-1 right-3 h-3 w-auto",
        card: `relative flex flex-col items-start gap-6 overflow-hidden ${island} ${islandPad} sm:flex-row`,
        glowLayer:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        glowAmber:
            "absolute -top-16 -left-10 h-40 w-40 rounded-full bg-app-accent/20 blur-3xl",
        glowRose:
            "absolute -bottom-20 right-0 h-48 w-48 rounded-full bg-app-accent/12 blur-3xl",
        mainCol: "min-w-0 flex-1",
        breadcrumbsWrap: `mb-2 text-xs ${shellColorRoles.muted}`,
        breadcrumbsNav: "flex flex-wrap items-center gap-1",
        crumbRow: "flex items-center gap-1",
        crumbText: "truncate",
        crumbSep: "opacity-60",
        title: `mb-2 text-2xl font-normal ${shellColorRoles.accent} sm:text-3xl lg:text-4xl`,
        description: `text-sm ${shellColorRoles.canvasFg}`,
        imageWrap:
            "h-28 w-28 shrink-0 overflow-hidden rounded-none border border-app-accent/20 bg-neutral-900/45 sm:h-32 sm:w-32",
        image: "h-full w-full object-cover",
    },

    contentBlock: {
        section: `relative overflow-hidden ${island} ${islandPad}`,
        glowLayer:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        glowAmberTR:
            "absolute -top-10 -right-10 h-32 w-32 rounded-full bg-app-accent/10 blur-3xl",
        glowRoseBL:
            "absolute bottom-0 left-0 h-24 w-24 rounded-full bg-app-accent/12 blur-3xl",
        inner: "relative",
        headerRow:
            "mb-5 flex flex-col gap-3 border-b border-app-accent/20 pb-4 sm:flex-row sm:items-end sm:justify-between",
        accentBar: "mb-2 h-px w-14 bg-gradient-to-r from-app-accent to-transparent",
        title: `text-xl font-normal ${shellColorRoles.accent} sm:text-2xl lg:text-3xl`,
        subtitle:
            `max-w-xl text-xs uppercase tracking-[0.22em] ${shellColorRoles.muted}`,
        body: `space-y-3 text-sm leading-relaxed ${shellColorRoles.canvasFg}`,
    },
} as const;
