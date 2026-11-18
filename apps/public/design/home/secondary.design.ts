/**
 * Вторичные страницы: контентный блок.
 */

import { secondarySurfaces, shellColorRoles } from "../layout/shell.design";

const island = secondarySurfaces.island;
const islandPad = secondarySurfaces.islandPad;

export const secondaryMarketingDesign = {
    contentBlock: {
        section: `relative overflow-hidden ${island} ${islandPad}`,
        glowLayer: "hidden",
        glowAmberTR: "hidden",
        glowRoseBL: "hidden",
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
