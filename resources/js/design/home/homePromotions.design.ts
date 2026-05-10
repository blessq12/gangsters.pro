/**
 * Блок акций на главной (комбо / desktop-only / mobile-only).
 */

export const homePromotionsDesign = {
    shared: {
        section: "my-12",
        thumbWrap: "aspect-[16/9] w-full overflow-hidden rounded-none",
        thumbImg:
            "h-full w-full object-cover grayscale transition-transform duration-500 ease-out group-hover:scale-105 group-hover:grayscale-0",
        modalStack: "space-y-3",
        modalMedia: "aspect-[16/9] w-full overflow-hidden rounded-none",
        modalImg: "h-full w-full object-cover",
        modalText: "text-sm leading-relaxed text-app-canvas-fg",
        emptyText: "py-4 text-center text-xs text-app-muted",
        swiperHook: "promos-swiper",
        pulseInner: "aspect-[16/9] w-full rounded-none",
    },

    combo: {
        heading: "mb-4 text-lg sm:text-xl font-semibold text-app-canvas-fg",
        mobileScroll:
            "promos-scroll flex gap-3 overflow-x-auto px-1 pb-2 snap-x snap-mandatory",
        mobileSkeletonOuter:
            "snap-start flex-none w-[18rem] rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        mobileArticle:
            "snap-start group relative flex-none w-[18rem] cursor-pointer rounded-none",
        desktopGrid:
            "mx-auto grid grid-cols-4 justify-items-center gap-4",
        desktopSkeleton:
            "aspect-[16/9] w-full max-w-xs rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        desktopArticle:
            "group relative w-full max-w-xs cursor-pointer rounded-none",
        emptyDesktopSpan: "col-span-4 py-4 text-center text-xs text-app-muted",
    },

    desktopSplit: {
        heading: "mb-4 text-xl font-semibold text-app-canvas-fg",
        grid: "mx-auto grid grid-cols-4 justify-items-center gap-4",
        skeleton:
            "aspect-[16/9] w-full max-w-xs rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        article:
            "group relative w-full max-w-xs cursor-pointer rounded-none",
        emptySpan: "col-span-4 py-4 text-center text-xs text-app-muted",
    },

    mobileSplit: {
        heading: "mb-4 text-lg font-semibold text-app-canvas-fg",
        loadingRow: "flex gap-3 overflow-x-auto px-1 pb-2",
        loadingCard:
            "flex-none w-[18rem] rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        article:
            "group relative cursor-pointer overflow-hidden rounded-none",
    },
} as const;
