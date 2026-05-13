/**
 * Полоса расписания компании (WorkScheduleStrip): триггер и плавающая панель.
 */

export const workScheduleDesign = {
    stripRoot: "w-full opacity-0",

    outerRow:
        "mx-auto mt-3 flex max-w-7xl justify-center px-4 pt-2 sm:mt-4 sm:px-6 md:max-w-none lg:px-8",

    /** Базовый каркас кнопки; поверх — theme.barSurface из `theme`. */
    triggerStatic:
        "inline-flex w-max max-w-[min(90vw,24rem)] shrink-0 items-center gap-2 rounded-none border px-3 py-2 text-left text-sm outline-none backdrop-blur-sm transition-colors focus-visible:ring-2 focus-visible:ring-app-accent/60 disabled:cursor-not-allowed disabled:opacity-60 md:max-w-[min(90vw,28rem)] sm:px-4",

    dotSize: "h-2.5 w-2.5 shrink-0 rounded-none sm:h-3 sm:w-3",

    dot: {
        loading: "bg-slate-400/90 shadow-none animate-pulse",
        noCompany: "bg-slate-500 shadow-none",
        open: "bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.7)]",
        closed: "bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.7)]",
    },

    summaryTruncate:
        "max-w-[min(65vw,16rem)] truncate font-medium sm:max-w-[min(50vw,18rem)] md:max-w-[20rem]",

    accentToggleHidden: "hidden shrink-0 text-xs font-medium sm:inline",

    chevronIcon:
        "mdi mdi-chevron-down shrink-0 text-lg text-app-muted transition-transform duration-200",

    /** Панель teleport: статика + theme.panelSurface */
    panelStatic:
        "fixed z-[80] overflow-y-auto rounded-none border px-4 py-3 backdrop-blur-sm sm:px-5 sm:py-3.5",

    panelParagraphToday:
        "mb-3 pb-2.5 text-xs leading-snug",

    panelParagraphByDays: "mb-3 text-xs",

    panelFallbackParagraph:
        "text-sm leading-relaxed tabular-nums",

    ulSchedule: "divide-y text-sm",

    liScheduleRow:
        "flex items-baseline justify-between gap-4 py-2 first:pt-0 last:pb-0",

    dayCell: "w-8 shrink-0 text-xs font-medium tabular-nums",

    workCell:
        "min-w-0 text-right text-sm tabular-nums",

    emptyState: "text-sm leading-relaxed",

    srOnlyToday: "sr-only",

    theme: {
        barSurface:
            "border-black/12 bg-app-canvas shadow-[0_0_20px_rgba(0,0,0,0.45)] hover:bg-black/[0.06]",
        panelSurface:
            "border-app-border-on-surface bg-[rgba(0,0,0,0.88)] shadow-xl backdrop-blur-lg",
        panelDivide: "divide-black/15",
        panelHeaderBorder: "border-b border-black/15",
        panelPrimaryText: "text-app-canvas-fg",
        panelMutedText: "text-app-muted",
        panelAccent: "text-app-accent",
        summaryLine: "text-app-canvas-fg",
        todayDayLabel: "text-app-accent",
    },
} as const;

export type WorkScheduleDesign = typeof workScheduleDesign;
