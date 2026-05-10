/**
 * Карусель главного баннера. Сложная геометрия карточек — в scoped HomeJumbotronBase.
 */

export const homeJumbotronDesign = {
    shared: {
        backdropLayer:
            "pointer-events-none absolute inset-0 opacity-50 mix-blend-screen",
        cardFrame:
            "home-jumbotron-card relative w-full max-w-full overflow-hidden rounded-none border bg-slate-900/60",
        gradientScrim:
            "pointer-events-none absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/85",
        swiperOverflow: "!overflow-visible",
        mediaSlot: "home-jumbotron-media home-jumbotron-aspect-slot relative",
        slideImage: "absolute inset-0 h-full w-full object-cover",
        loadingSlot:
            "home-jumbotron-aspect-slot rounded-none border border-white/10 bg-slate-800/60 animate-pulse",
        navRail:
            "pointer-events-none absolute inset-y-0 left-0 right-0 z-20 hidden items-center justify-between px-5 md:flex lg:px-8",
        navBtn:
            "pointer-events-auto inline-flex h-11 w-11 items-center justify-center rounded-none border border-white/15 bg-black/45 text-slate-100 backdrop-blur transition hover:border-amber-300/40 hover:text-amber-200",
        emptyState: "mx-auto max-w-4xl py-8 text-center text-xs text-slate-500",
        slideInnerFlex: "flex min-w-0 justify-center px-0.5",
    },

    mobile: {
        sectionRoot:
            "home-jumbotron home-jumbotron--mobile relative mt-6 mb-14 w-screen max-w-none overflow-x-clip [margin-left:calc(50%-50vw)] [margin-right:calc(50%-50vw)]",
        glowLeft:
            "absolute -left-8 top-6 h-32 w-32 rounded-full bg-amber-500/15 blur-3xl",
        glowRight:
            "absolute -right-8 bottom-0 h-40 w-40 rounded-full bg-rose-500/10 blur-3xl",
        innerWrap: "relative overflow-x-clip px-3 sm:px-4",
        loadingRow: "flex justify-center px-1 py-6 sm:py-7",
        slidePadY: "py-2",
        badgeBrand:
            "absolute left-3 top-3 inline-flex rounded-none border border-white/10 bg-[rgba(0,0,0,0.42)] px-2 py-[3px] text-[10px] font-medium uppercase tracking-[0.2em] text-slate-100 backdrop-blur",
        badgeCounter:
            "absolute right-3 top-3 inline-flex rounded-none border border-amber-400/20 bg-[rgba(0,0,0,0.38)] px-2 py-[3px] text-[10px] font-semibold text-amber-200 backdrop-blur",
        captionPanel:
            "absolute inset-x-2.5 bottom-2 rounded-none border border-white/10 bg-[rgba(0,0,0,0.32)] px-3 py-2.5 backdrop-blur-xl",
        title:
            "text-xl font-semibold leading-tight text-amber-300",
        description: "mt-1 text-xs leading-relaxed text-slate-200/90",
    },

    desktop: {
        sectionRoot:
            "home-jumbotron home-jumbotron--desktop relative mt-8 mb-12 w-screen max-w-none overflow-hidden sm:mt-12 sm:mb-18 [margin-left:calc(50%-50vw)] [margin-right:calc(50%-50vw)]",
        glowLeft:
            "absolute -left-10 top-8 h-36 w-36 rounded-full bg-amber-500/15 blur-3xl sm:-left-16 sm:top-0 sm:h-56 sm:w-56",
        glowRight:
            "absolute -right-8 bottom-0 h-44 w-44 rounded-full bg-rose-500/10 blur-3xl sm:right-0 sm:h-64 sm:w-64",
        innerWrap: "relative px-4 sm:px-6 lg:px-8",
        loadingRow: "flex justify-center px-1 py-6 sm:py-8",
        slidePadY: "py-3 sm:py-4",
        badgeBrand:
            "absolute left-3 top-3 inline-flex rounded-none border border-white/10 bg-[rgba(0,0,0,0.42)] px-2.5 py-1 text-[10px] font-medium uppercase tracking-[0.2em] text-slate-100 backdrop-blur sm:left-4 sm:top-4 sm:text-[11px]",
        badgeCounter:
            "absolute right-3 top-3 inline-flex rounded-none border border-amber-400/20 bg-[rgba(0,0,0,0.38)] px-2.5 py-1 text-[10px] font-semibold text-amber-200 backdrop-blur sm:right-4 sm:top-4 sm:text-[11px]",
        captionPanel:
            "absolute inset-x-3 bottom-3 rounded-none border border-white/10 bg-[rgba(0,0,0,0.32)] px-4 py-3 backdrop-blur-xl sm:inset-x-0 sm:bottom-0 sm:rounded-none sm:border-0 sm:bg-transparent sm:px-6 sm:py-4 sm:backdrop-blur-0",
        title:
            "text-xl font-semibold leading-tight text-amber-300 sm:text-2xl",
        description:
            "mt-1 max-w-[18rem] text-xs leading-relaxed text-slate-200/90 sm:max-w-none sm:text-sm",
    },
} as const;
