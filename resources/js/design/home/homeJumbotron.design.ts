/**
 * Карусель главного баннера. Сложная геометрия карточек — в scoped HomeJumbotronBase.
 */

export const homeJumbotronDesign = {
    shared: {
        backdropLayer:
            "pointer-events-none absolute inset-0 opacity-50 mix-blend-screen",
        cardFrame:
            "home-jumbotron-card relative w-full max-w-full overflow-hidden rounded-none border border-transparent bg-neutral-950/72",
        swiperOverflow: "!overflow-visible",
        mediaSlot: "home-jumbotron-media home-jumbotron-aspect-slot relative",
        slideImage: "absolute inset-0 h-full w-full object-cover",
        loadingSlot:
            "home-jumbotron-aspect-slot rounded-none bg-neutral-900/65 animate-pulse",
        navRail:
            "pointer-events-none absolute inset-y-0 left-0 right-0 z-20 hidden items-center justify-between px-5 md:flex lg:px-8",
        navBtn:
            "pointer-events-auto inline-flex h-11 w-11 items-center justify-center rounded-none border border-transparent bg-[rgba(0,0,0,0.82)] text-app-canvas-fg backdrop-blur-xl transition hover:border-app-accent/40 hover:text-app-accent",
        emptyState: "mx-auto max-w-4xl py-8 text-center text-xs text-app-muted",
        slideInnerFlex: "flex min-w-0 justify-center px-0.5",
    },

    mobile: {
        sectionRoot:
            "home-jumbotron home-jumbotron--mobile relative mt-6 mb-14 w-screen max-w-none overflow-x-clip [margin-left:calc(50%-50vw)] [margin-right:calc(50%-50vw)]",
        glowLeft:
            "absolute -left-8 top-6 h-32 w-32 rounded-full bg-app-accent/15 blur-3xl",
        glowRight:
            "absolute -right-8 bottom-0 h-40 w-40 rounded-full bg-app-accent/12 blur-3xl",
        innerWrap: "relative overflow-x-clip px-3 sm:px-4",
        loadingRow: "flex justify-center px-1 py-6 sm:py-7",
        slidePadY: "py-2",
    },

    desktop: {
        sectionRoot:
            "home-jumbotron home-jumbotron--desktop relative mt-8 mb-12 w-screen max-w-none overflow-hidden sm:mt-12 sm:mb-18 [margin-left:calc(50%-50vw)] [margin-right:calc(50%-50vw)]",
        glowLeft:
            "absolute -left-10 top-8 h-36 w-36 rounded-full bg-app-accent/15 blur-3xl sm:-left-16 sm:top-0 sm:h-56 sm:w-56",
        glowRight:
            "absolute -right-8 bottom-0 h-44 w-44 rounded-full bg-app-accent/12 blur-3xl sm:right-0 sm:h-64 sm:w-64",
        innerWrap: "relative px-4 sm:px-6 lg:px-8",
        loadingRow: "flex justify-center px-1 py-6 sm:py-8",
        slidePadY: "py-3 sm:py-4",
    },
} as const;
