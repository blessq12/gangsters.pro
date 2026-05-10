/**
 * Оболочка приложения: корневой flex, интро, контейнер main.
 * Фон темы и .app-shell — в scoped MainLayout* (градиенты).
 */

export const layoutShellDesign = {
    shared: {
        root: "app-shell min-h-screen flex flex-col",
        themeDark: "theme-dark text-slate-50",
        themeLight: "theme-light text-slate-900",
        introOverlay:
            "pointer-events-none fixed inset-0 z-40 flex items-center justify-center",
        mainGrow: "flex-1",
    },

    core: {
        introLogo: "h-40 w-auto md:h-48",
        mainContainer:
            "mx-auto max-w-7xl px-4 pb-5 pt-0 opacity-0 sm:px-6 lg:px-8",
    },

    desktop: {
        introLogo: "h-44 w-auto md:h-52",
        mainContainer:
            "mx-auto max-w-7xl px-6 pb-7 pt-0 opacity-0 lg:px-8",
    },

    mobile: {
        introLogo: "h-40 w-auto md:h-48",
        mainContainer: "mx-auto max-w-7xl px-4 pb-3 pt-0 opacity-0 sm:px-6",
    },

    /** SecondaryPageLayout: крупный hero вторичных страниц */
    secondaryPage: {
        section: "relative mt-12 mb-12",
        outerGlowWrap:
            "pointer-events-none absolute inset-0 opacity-40 mix-blend-screen",
        outerGlowTL:
            "absolute -top-24 -left-10 h-56 w-56 rounded-full bg-amber-500/15 blur-3xl",
        outerGlowBR:
            "absolute -bottom-24 right-0 h-64 w-64 rounded-full bg-rose-500/10 blur-3xl",
        heroCard:
            "relative overflow-hidden rounded-none border border-white/10 bg-slate-900/60 shadow-[0_20px_80px_rgba(0,0,0,0.55)]",
        heroImageLayer: "absolute inset-0",
        heroImage: "h-full w-full object-cover opacity-55",
        heroScrim:
            "absolute inset-0 bg-[linear-gradient(135deg,rgba(0,0,0,0.92)_0%,rgba(0,0,0,0.66)_45%,rgba(0,0,0,0.4)_100%)]",
        innerAmbient: "absolute inset-0",
        innerGlowA:
            "absolute -left-20 top-10 h-52 w-52 rounded-full bg-amber-500/10 blur-3xl",
        innerGlowB:
            "absolute top-0 right-0 h-60 w-60 rounded-full bg-rose-500/10 blur-3xl",
        contentPad:
            "relative px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12",
        sticksWrap: "rotate-15",
        stickLeft:
            "pointer-events-none absolute -bottom-2 right-6 h-3 w-auto",
        stickRight:
            "pointer-events-none absolute bottom-1 right-3 h-3 w-auto",
        textCol: "min-w-0",
        breadcrumbsNav:
            "mb-4 flex flex-wrap items-center gap-1 text-xs text-slate-400",
        breadcrumbHomeLink:
            "transition-colors hover:text-amber-300",
        breadcrumbText: "text-slate-300",
        breadcrumbSep: "opacity-60",
        eyebrow:
            "mb-3 inline-flex rounded-none border border-amber-400/30 bg-[rgba(255,255,255,0.05)] px-3 py-1 text-[11px] uppercase tracking-[0.28em] text-amber-200 backdrop-blur",
        title:
            "mb-3 max-w-3xl text-2xl font-semibold leading-tight text-amber-300 sm:text-3xl lg:text-4xl",
        description:
            "max-w-2xl text-sm leading-relaxed text-slate-200/90 sm:text-base",
        statsGrid: "mt-6 grid gap-3 sm:grid-cols-3",
        statCard:
            "rounded-none border border-white/10 bg-[rgba(255,255,255,0.05)] px-4 py-3 backdrop-blur",
        statLabel:
            "text-[11px] uppercase tracking-[0.22em] text-slate-400",
        statValue:
            "mt-1 text-base font-semibold text-slate-50 sm:text-lg",
        slotWrap: "space-y-8 sm:space-y-10",
    },
} as const;
