/**
 * Классы навбара: общие правила и три варианта разметки (responsive / desktop / mobile).
 */

export const navbarDesign = {
    shared: {
        header: "pt-4",
        linkTransition: "transition-colors duration-200",
        linkActive: "text-amber-300",
        linkInactive: "text-slate-200/80 hover:text-white",
        logoLink: "inline-flex items-center justify-center group",
        mdiIcon: "mdi text-lg",
        burgerOpen: "border-amber-400/70 text-amber-200",
        burgerClosedHover: "hover:border-amber-400/50 hover:text-amber-200",
    },

    responsive: {
        inner: "mx-auto max-w-7xl px-4 sm:px-6 lg:px-8",
        bar: "flex items-center justify-between gap-4 rounded-none border border-amber-400/40 bg-[rgba(255,255,255,0.04)]/80 px-4 sm:px-6 lg:px-8 py-3.5 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur",
        leftZone:
            "flex min-w-0 items-center gap-2 sm:gap-3 w-24 sm:w-auto",
        balanceSpacer: "w-10 shrink-0 md:hidden",
        desktopNavGate: "hidden min-w-0 items-center md:flex",
        navLeft:
            "flex items-center gap-4 text-sm font-medium tracking-wide",
        logoRow: "text-lg font-semibold flex-1 flex justify-center",
        logoImg:
            "h-9 min-h-9 sm:h-10 sm:min-h-10 md:h-11 md:min-h-11 w-auto min-w-[7rem] max-w-full mx-auto object-contain drop-shadow-[0_0_15px_rgba(251,191,36,0.45)] group-hover:scale-105 group-hover:drop-shadow-[0_0_22px_rgba(251,191,36,0.7)] transition-transform duration-200",
        rightZone:
            "flex items-center justify-end gap-2 sm:gap-3 w-24 sm:w-auto",
        burgerButton:
            "flex h-9 w-9 items-center justify-center rounded-none border border-white/20 bg-black/70 text-slate-200 transition-colors md:hidden",
        navRight:
            "hidden md:flex items-center gap-4 text-sm justify-end font-medium tracking-wide",
    },

    desktop: {
        inner: "mx-auto max-w-7xl px-6 lg:px-8",
        bar: "flex items-center justify-between gap-4 rounded-none border border-amber-400/40 bg-[rgba(255,255,255,0.04)]/80 px-4 sm:px-6 lg:px-8 py-3.5 shadow-[0_0_25px_rgba(0,0,0,0.7)] backdrop-blur",
        leftZone: "flex min-w-0 items-center",
        navLeft:
            "flex items-center gap-4 text-sm font-medium tracking-wide",
        logoRow: "text-lg font-semibold flex-1 flex justify-center",
        logoImg:
            "h-10 min-h-10 w-auto min-w-[7rem] max-w-full mx-auto object-contain drop-shadow-[0_0_15px_rgba(251,191,36,0.45)] group-hover:scale-105 group-hover:drop-shadow-[0_0_22px_rgba(251,191,36,0.7)] transition-transform duration-200",
        rightZone: "flex items-center justify-end gap-4 w-48",
        navRight:
            "flex items-center gap-4 text-sm font-medium tracking-wide",
    },

    mobile: {
        inner: "mx-auto max-w-7xl px-4",
        bar: "flex items-center justify-between gap-4 rounded-none border border-amber-400/40 bg-[rgba(255,255,255,0.06)] px-4 py-3.5 shadow-[0_0_25px_rgba(0,0,0,0.7)]",
        leftSpacer: "w-10 shrink-0",
        logoRow: "text-lg font-semibold flex-1 flex justify-center",
        logoPulseWrap:
            "inline-flex origin-center will-change-transform",
        logoImg:
            "h-9 min-h-9 w-auto min-w-[7rem] max-w-full mx-auto object-contain transition-transform duration-200 group-hover:scale-105",
        burgerZone: "flex items-center justify-end",
        burgerButton:
            "flex h-9 w-9 items-center justify-center rounded-none border border-white/20 bg-black/70 text-slate-200 transition-colors",
    },

    mobileMenu: {
        overlayRoot:
            "pointer-events-none fixed inset-x-0 top-0 z-30 md:hidden pt-24",
        innerContainer:
            "pointer-events-auto mx-auto mt-3 max-w-7xl px-4 sm:px-6 lg:px-8",
        sheetNav:
            "overflow-hidden rounded-none border border-white/10 bg-[#1f1f23] text-sm font-medium text-slate-50 shadow-[0_20px_50px_rgba(0,0,0,0.75)]",
        companySection: "border-b border-white/10 px-4 py-3",
        companyTitle:
            "text-[13px] font-semibold leading-snug text-slate-50",
        companyTagline:
            "mt-0.5 line-clamp-2 text-xs leading-snug text-slate-400",
        companySchedule:
            "mt-2.5 text-[11px] leading-snug text-slate-400",
        companyAddress:
            "mt-1.5 text-[11px] leading-snug text-slate-500",
        phoneLink:
            "mt-2.5 inline-flex text-xs font-medium text-amber-400/95 hover:text-amber-300",
        linksRegion: "space-y-0.5 px-2 py-2",
        routerLinkItem:
            "block rounded-none px-3 py-2.5 text-slate-200 hover:bg-white/5",
    },
} as const;

export type NavbarDesign = typeof navbarDesign;
