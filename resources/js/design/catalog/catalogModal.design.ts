/**
 * Модалка товара и связанные блоки (инфо поверх медиа, галерея внутри модалки).
 */

export const catalogModalDesign = {
    shell: {
        root:
            "fixed inset-0 z-[9999] flex items-center justify-center",
        backdrop: "absolute inset-0 bg-neutral-950/85",
        backdropMobile: "absolute inset-0 bg-neutral-950/85",
        content:
            "relative z-[1] flex w-full max-h-screen items-center justify-center overflow-hidden p-6 lg:p-8",
        contentMobile: "p-[0.35rem_0.6rem]",
        wrapper: "m-auto w-full max-w-5xl",
        wrapperMobile: "m-auto w-full max-w-full",
        panel:
            "relative aspect-video max-h-[90vh] w-full overflow-hidden bg-[rgba(20,20,20,0.88)] shadow-[0_25px_60px_rgba(0,0,0,0.9)]",
        panelMobile:
            "relative h-[92dvh] max-h-[100dvh] w-full min-h-0 overflow-hidden bg-[rgba(31,31,35,0.65)] shadow-[0_25px_60px_rgba(0,0,0,0.92)]",
        closeBtn:
            "absolute right-4 top-4 z-[3] flex h-9 w-9 items-center justify-center border border-transparent bg-neutral-950/85 text-xl text-app-muted transition-colors hover:border-app-accent/50 hover:text-app-accent",
        closeBtnMobile: "left-3 right-auto top-3",
        closeIcon: "mdi mdi-close",
        body: "absolute inset-0 z-0",
        /** Зона галереи: отдельный градиентный оверлей в шаблоне (дочерний div). */
        mediaZone:
            "absolute inset-0 z-0 [&_.product-gallery]:h-full [&_.product-gallery]:min-h-full [&_.product-gallery]:w-full",
        mediaOverlayDesktop:
            "pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/95 via-black/55 to-transparent",
        mediaOverlayMobile:
            "pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/95 via-black/60 to-transparent",
        emptyCopy: "Нет данных о товаре.",
        empty:
            "absolute inset-0 z-[1] m-0 flex items-center justify-center p-8 text-base text-app-muted",
        emptyMobile:
            "static flex items-center justify-center p-8 text-sm text-app-muted",
        infoFooterOverlay:
            "absolute inset-x-0 bottom-0 z-[2] flex max-h-[55%] min-w-0 flex-col justify-end overflow-y-auto px-4 pb-4 pt-2 sm:px-5 sm:pb-5",
        infoFooterOverlayMobile:
            "inset-x-[0.6rem] bottom-[0.6rem] max-h-[58%] px-0 pb-0",
    },

    detailInfo: {
        card: "flex flex-col gap-3",
        titleCol: "min-w-0",
        title:
            "font-heading text-lg font-normal leading-snug text-app-canvas-fg line-clamp-3 sm:text-xl",
        tagsRow: "mt-2 flex flex-wrap gap-1.5",
        tagPill:
            "inline-flex items-center px-2 py-0.5 text-[11px] font-medium backdrop-blur",
        controlsRow:
            "flex w-full min-w-0 shrink-0 flex-nowrap items-center gap-2",
        actionIsland:
            "relative flex w-fit shrink-0 flex-nowrap items-center gap-1.5 bg-[rgba(0,0,0,0.78)] px-2 py-1 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur-xl",
        favBtn:
            "flex h-11 w-11 shrink-0 items-center justify-center border border-transparent bg-neutral-950/85 text-app-canvas-fg transition-colors hover:border-app-accent/60 hover:text-app-accent",
        favBtnActive: "border-app-accent/60 text-app-accent",
        favIcon: "mdi text-xl",
        nutritionBtn:
            "flex h-10 w-10 shrink-0 items-center justify-center border border-app-accent/40 bg-neutral-950/85 text-app-accent transition-colors hover:border-app-accent/70 hover:text-app-accent",
        nutritionIcon: "mdi mdi-fire-circle text-xl",
        ingredientsBtn:
            "flex h-10 w-10 shrink-0 items-center justify-center border border-transparent bg-neutral-950/85 text-app-canvas-fg transition-colors hover:border-app-accent/50 hover:text-app-accent",
        ingredientsIcon: "mdi mdi-information-outline text-xl",
        cartIconOuter: "flex h-10 shrink-0 items-center",
        cartAddIconBtn:
            "relative inline-flex h-10 w-10 shrink-0 items-center justify-center bg-app-accent text-black transition-transform hover:scale-[1.03]",
        cartIcon: "mdi mdi-cart-outline text-xl",
        qtyCluster:
            "flex h-10 shrink-0 items-center gap-0.5 border border-app-accent/50 bg-neutral-950/85 px-0.5",
        qtyMiniBtn:
            "flex h-9 w-9 shrink-0 items-center justify-center bg-neutral-950/85 text-base font-semibold leading-none text-app-canvas-fg",
        qtyNum:
            "min-w-[1.5rem] px-0.5 text-center text-sm font-semibold tabular-nums text-app-accent",
        priceBtn:
            "ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap bg-app-accent px-3 py-1.5 text-sm font-semibold text-black transition-transform duration-200 hover:scale-[1.03] cursor-pointer",
        teleportTooltipBase:
            "fixed z-[10000] bg-[rgba(0,0,0,0.95)] px-3 py-2.5 shadow-xl backdrop-blur max-h-44 overflow-y-auto",
        tooltipWide: "w-[260px]",
        tooltipNarrow: "w-[240px]",
        nutritionBlock: "space-y-2 text-xs text-app-canvas-fg",
        nutritionRow:
            "flex items-center justify-between gap-2",
        nutritionLabel: "text-app-muted",
        nutritionVal: "font-medium",
        ingredientsBlock: "space-y-2 text-xs text-app-canvas-fg",
        ingredientsHeading:
            "text-[11px] font-medium text-app-muted",
        ingredientsBody: "break-words text-app-canvas-fg/90",
    },

    gallery: {
        root: "product-gallery relative w-full h-64 bg-neutral-800/50 overflow-hidden sm:h-80",
        noPhotoLabel: "Нет фото",
        noPhoto:
            "flex h-full w-full items-center justify-center text-sm text-app-muted",
        swiper: "product-gallery__swiper w-full h-full",
        slide: "product-gallery__slide w-full h-full",
        wrap: "product-gallery__wrap w-full h-full",
        img: "product-gallery__img w-full h-full object-cover object-center",
    },
} as const;

export type CatalogModalDesign = typeof catalogModalDesign;
