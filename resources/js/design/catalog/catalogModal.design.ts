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
            "pointer-events-none relative z-[1] flex w-full max-h-screen items-center justify-center overflow-visible",
        contentDesktop: "p-6 lg:p-8",
        contentMobile: "p-[0.35rem_0.6rem]",
        wrapper: "m-auto w-full max-w-5xl",
        wrapperMobile: "m-auto w-full max-w-full",
        panel:
            "pointer-events-auto relative aspect-video max-h-[90vh] w-full overflow-hidden bg-[rgba(20,20,20,0.88)] shadow-[0_25px_60px_rgba(0,0,0,0.9)]",
        panelMobile:
            "pointer-events-auto relative h-[92dvh] max-h-[100dvh] w-full min-h-0 overflow-hidden bg-[rgba(31,31,35,0.65)] shadow-[0_25px_60px_rgba(0,0,0,0.92)]",
        closeBtn:
            "absolute right-4 top-4 z-[3] flex h-9 w-9 items-center justify-center border border-transparent bg-black/45 text-xl text-app-muted backdrop-blur transition-colors hover:border-app-accent/50 hover:text-app-accent",
        closeBtnInline:
            "flex h-9 w-9 shrink-0 items-center justify-center border border-transparent bg-black/45 text-xl text-app-muted backdrop-blur transition-colors hover:border-app-accent/50 hover:text-app-accent",
        closeIcon: "mdi mdi-close",
        body: "absolute inset-0 z-0",
        /** Зона галереи: отдельный градиентный оверлей в шаблоне (дочерний div). */
        mediaZone:
            "absolute inset-0 z-0 [&_.product-gallery]:h-full [&_.product-gallery]:min-h-full [&_.product-gallery]:w-full",
        mediaOverlayDesktop:
            "pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/50 via-black/20 to-transparent",
        mediaOverlayMobile:
            "pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/50 via-black/20 to-transparent",
        emptyCopy: "Нет данных о товаре.",
        empty:
            "absolute inset-0 z-[1] m-0 flex items-center justify-center p-8 text-base text-app-muted",
        emptyMobile:
            "static flex items-center justify-center p-8 text-sm text-app-muted",
        infoOverlay:
            "pointer-events-none absolute inset-0 z-[2] flex flex-col justify-between",
        infoHeader:
            "pointer-events-auto w-full max-w-full px-4 pt-4 pb-2 sm:px-5 sm:pt-5",
        infoHeaderMobile: "px-3",
        infoFooter:
            "pointer-events-auto flex max-h-[55%] min-w-0 flex-col justify-end overflow-y-auto px-4 pb-4 pt-2 sm:px-5 sm:pb-5",
        infoFooterMobile:
            "mx-[0.6rem] max-h-[58%] px-0 pb-[0.6rem]",
    },

    detailInfo: {
        card: "flex flex-col gap-3",
        titleCol: "min-w-0 mb-3",
        headerBlock: "w-full",
        titleTopRow: "flex w-full items-start gap-2 sm:gap-3",
        headerContentCol: "flex min-w-0 flex-1 flex-col gap-0.5",
        title:
            "block w-full min-w-0 font-heading text-xl font-normal leading-tight text-app-canvas-fg line-clamp-2 drop-shadow-[0_2px_10px_rgba(0,0,0,0.85)] sm:text-2xl lg:text-3xl",
        metaLinksRow: "flex flex-wrap items-center gap-x-3 gap-y-0",
        metaLink:
            "block text-xs font-medium text-app-canvas-fg/85 underline-offset-2 transition-colors hover:text-app-accent hover:underline sm:text-sm",
        metaLinkActive: "text-app-accent underline",
        metaLinkMuted:
            "block text-xs font-medium text-app-canvas-fg/70 sm:text-sm",
        priceHeaderBtn:
            "inline-flex shrink-0 min-h-10 items-center whitespace-nowrap px-3 py-1.5 text-base font-bold tabular-nums text-white transition-transform duration-200 hover:scale-[1.02] sm:min-h-11 sm:px-4 sm:py-2 sm:text-xl lg:text-2xl",
        tagsRow: "mb-2 flex flex-wrap gap-1.5",
        tagPill:
            "inline-flex items-center px-2 py-0.5 text-[11px] font-medium backdrop-blur",
        controlsRow:
            "flex w-full min-w-0 shrink-0 flex-nowrap items-center justify-start gap-2",
        actionIsland:
            "relative flex w-fit shrink-0 flex-nowrap items-center gap-1.5 bg-black/45 px-2 py-1 backdrop-blur-xl",
        favBtn:
            "flex h-11 w-11 shrink-0 items-center justify-center border border-transparent bg-transparent text-app-canvas-fg transition-colors hover:border-app-accent/60 hover:text-app-accent",
        favBtnActive: "border-app-accent/60 text-app-accent",
        favIcon: "mdi text-xl",
        nutritionBtn:
            "flex h-10 w-10 shrink-0 items-center justify-center border border-app-accent/40 bg-transparent text-app-accent transition-colors hover:border-app-accent/70 hover:text-app-accent",
        nutritionIcon: "mdi mdi-fire-circle text-xl",
        ingredientsBtn:
            "flex h-10 w-10 shrink-0 items-center justify-center border border-transparent bg-transparent text-app-canvas-fg transition-colors hover:border-app-accent/50 hover:text-app-accent",
        ingredientsIcon: "mdi mdi-information-outline text-xl",
        cartIconOuter: "flex h-10 shrink-0 items-center",
        cartAddIconBtn:
            "inline-flex h-10 shrink-0 items-center justify-center bg-app-accent px-4 py-2 text-xs font-bold uppercase tracking-wide text-black transition-transform hover:scale-[1.03] sm:text-sm",
        cartIcon: "mdi mdi-cart-outline text-xl",
        qtyCluster:
            "flex h-10 shrink-0 items-center gap-0.5 border border-app-accent/50 bg-transparent px-0.5",
        qtyMiniBtn:
            "flex h-9 w-9 shrink-0 items-center justify-center bg-transparent text-base font-semibold leading-none text-app-canvas-fg",
        qtyNum:
            "min-w-[1.5rem] px-0.5 text-center text-sm font-semibold tabular-nums text-app-accent",
        priceBtn:
            "ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap bg-app-accent px-3 py-1.5 text-sm font-semibold text-black transition-transform duration-200 hover:scale-[1.03] cursor-pointer",
        teleportTooltipBase:
            "fixed z-[10000] bg-black/80 px-3 py-2.5 shadow-xl backdrop-blur max-h-44 overflow-y-auto",
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
        setBadge:
            "inline-flex items-center border border-violet-400/50 bg-violet-500/20 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-violet-100 backdrop-blur",
        description:
            "text-xs leading-relaxed text-app-canvas-fg/85 line-clamp-4",
        compositionBtn:
            "flex h-10 w-10 shrink-0 items-center justify-center border border-violet-400/40 bg-transparent text-violet-200 transition-colors hover:border-violet-400/70 hover:text-violet-100",
        compositionIcon: "mdi mdi-format-list-bulleted text-xl",
        compositionBlock: "space-y-2 text-xs text-app-canvas-fg",
        compositionHeading:
            "text-[11px] font-medium text-app-muted",
        compositionRow:
            "flex items-start justify-between gap-3",
        compositionName: "min-w-0 break-words text-app-canvas-fg/90",
        compositionQty: "shrink-0 font-medium tabular-nums text-app-accent",
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
