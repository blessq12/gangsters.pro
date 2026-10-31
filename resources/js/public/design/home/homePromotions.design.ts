/**
 * Блок акций на главной (комбо / desktop-only / mobile-only).
 */

export const homePromotionsDesign = {
    shared: {
        section: "my-12",
        thumbWrap: "w-full rounded-none",
        thumbImg: "block h-auto w-full max-w-full object-contain",
        modalStack: "space-y-3",
        modalMedia: "w-full overflow-hidden rounded-none",
        modalImg: "mx-auto block h-auto w-full max-w-full max-h-[70vh] object-contain",
        modalText: "text-sm leading-relaxed text-app-canvas-fg",
        emptyText: "py-4 text-center text-xs text-app-muted",
        swiperHook: "promos-swiper",
        pulseInner: "min-h-[8rem] w-full rounded-none",
    },

    combo: {
        heading:
            "mb-4 text-xl font-normal text-app-accent sm:text-2xl lg:text-3xl",
        mobileScroll:
            "promos-scroll flex gap-3 overflow-x-auto px-1 pb-2 snap-x snap-mandatory",
        mobileSkeletonOuter:
            "snap-start flex-none w-[18rem] rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        mobileArticle:
            "snap-start relative flex-none w-[18rem] cursor-pointer rounded-none transition-transform duration-500 ease-out hover:scale-[1.03]",
        desktopGrid:
            "mx-auto grid grid-cols-4 justify-items-center gap-4",
        desktopSkeleton:
            "min-h-[8rem] w-full max-w-xs rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        desktopArticle:
            "relative w-full max-w-xs cursor-pointer rounded-none transition-transform duration-500 ease-out hover:scale-[1.03]",
        emptyDesktopSpan: "col-span-4 py-4 text-center text-xs text-app-muted",
    },

    desktopSplit: {
        heading: "mb-4 text-2xl font-normal text-app-accent sm:text-3xl",
        grid: "mx-auto grid grid-cols-4 justify-items-center gap-4",
        skeleton:
            "min-h-[8rem] w-full max-w-xs rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        article:
            "relative w-full max-w-xs cursor-pointer rounded-none transition-transform duration-500 ease-out hover:scale-[1.03]",
        emptySpan: "col-span-4 py-4 text-center text-xs text-app-muted",
    },

    mobileSplit: {
        heading: "mb-4 text-xl font-normal text-app-accent sm:text-2xl",
        loadingRow: "flex gap-3 overflow-x-auto px-1 pb-2",
        loadingCard:
            "flex-none w-[18rem] rounded-none border border-black/12 bg-neutral-900/65 animate-pulse",
        article:
            "relative cursor-pointer rounded-none transition-transform duration-500 ease-out hover:scale-[1.03]",
    },
} as const;
