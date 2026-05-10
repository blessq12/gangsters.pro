/**
 * Вторичные страницы: баннер-герой и контентный блок.
 */

export const secondaryMarketingDesign = {
    banner: {
        section: "relative my-6 md:my-8 lg:my-12",
        sticksWrap: "rotate-15",
        stickLeft:
            "pointer-events-none absolute -bottom-2 right-6 h-3 w-auto",
        stickRight:
            "pointer-events-none absolute bottom-1 right-3 h-3 w-auto",
        card: "relative flex flex-col items-start gap-6 overflow-hidden rounded-none border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-8 sm:flex-row sm:px-6 sm:py-10 lg:px-8",
        glowLayer:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        glowAmber:
            "absolute -top-16 -left-10 h-40 w-40 rounded-full bg-amber-500/20 blur-3xl",
        glowRose:
            "absolute -bottom-20 right-0 h-48 w-48 rounded-full bg-rose-500/10 blur-3xl",
        mainCol: "min-w-0 flex-1",
        breadcrumbsWrap: "mb-2 text-xs text-slate-400",
        breadcrumbsNav: "flex flex-wrap items-center gap-1",
        crumbRow: "flex items-center gap-1",
        crumbText: "truncate",
        crumbSep: "opacity-60",
        title: "mb-2 text-xl font-semibold text-amber-300 sm:text-2xl",
        description: "text-sm text-slate-200/90",
        imageWrap:
            "h-28 w-28 shrink-0 overflow-hidden rounded-none border border-white/10 bg-slate-900/40 sm:h-32 sm:w-32",
        image: "h-full w-full object-cover",
    },

    contentBlock: {
        section:
            "relative overflow-hidden rounded-none border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-6 shadow-[0_16px_50px_rgba(0,0,0,0.35)] sm:px-6 sm:py-8 lg:px-8",
        glowLayer:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        glowAmberTR:
            "absolute -top-10 -right-10 h-32 w-32 rounded-full bg-amber-500/10 blur-3xl",
        glowRoseBL:
            "absolute bottom-0 left-0 h-24 w-24 rounded-full bg-rose-500/10 blur-3xl",
        inner: "relative",
        headerRow:
            "mb-5 flex flex-col gap-3 border-b border-white/10 pb-4 sm:flex-row sm:items-end sm:justify-between",
        accentBar: "mb-2 h-px w-14 bg-gradient-to-r from-amber-400 to-transparent",
        title: "text-lg font-semibold text-amber-300 sm:text-xl",
        subtitle:
            "max-w-xl text-xs uppercase tracking-[0.22em] text-slate-400",
        body: "space-y-3 text-sm leading-relaxed text-slate-200/90",
    },
} as const;
